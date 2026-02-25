<?php

namespace App\Http\Controllers;

use App\Models\Conto;
use App\Models\Incasso;
use App\Models\Member;
use App\Models\PrimaNotaEntry;
use App\Models\Settings;
use App\Models\Subscription;
use App\Services\ReceiptService;
use App\Services\RendicontoCassaSchema;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Gestione incassi (quote e donazioni). Opzione emissione ricevuta e prima nota.
 */
class IncassoController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,segreteria,contabile');
    }

    /**
     * Redirect per compatibilità: vecchi link incassi → quote sociali.
     */
    public function index(Request $request)
    {
        return redirect()->route('quote-sociali.index', $request->query());
    }

    /**
     * Elenco quote sociali (solo type = quota).
     */
    public function indexQuote(Request $request)
    {
        $query = Incasso::with(['member', 'subscription', 'conto', 'receipt'])
            ->where('type', Incasso::TYPE_QUOTA);

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('paid_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('paid_at', '<=', $request->to);
        }

        $incassi = $query->orderByDesc('paid_at')->paginate(20)->withQueryString();
        return Inertia::render('QuoteSociali/Index', [
            'incassi' => $incassi,
            'filters' => $request->only('member_id', 'from', 'to'),
            'members' => Member::orderBy('cognome')->orderBy('nome')->get(['id', 'nome', 'cognome']),
        ]);
    }

    /**
     * Elenco erogazioni liberali (solo type = donazione).
     */
    public function indexDonazioni(Request $request)
    {
        $query = Incasso::with(['member', 'conto', 'receipt'])
            ->where('type', Incasso::TYPE_DONAZIONE);

        if ($request->filled('from')) {
            $query->whereDate('paid_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('paid_at', '<=', $request->to);
        }

        $incassi = $query->orderByDesc('paid_at')->paginate(20)->withQueryString();
        return Inertia::render('Donazioni/Index', [
            'incassi' => $incassi,
            'filters' => $request->only('from', 'to'),
        ]);
    }

    public function create(Request $request)
    {
        $preselectedType = $request->get('type', 'quota');
        if (! in_array($preselectedType, ['quota', 'donazione'], true)) {
            $preselectedType = 'quota';
        }
        $memberId = $request->get('member_id');
        $subscriptionId = $request->get('subscription_id');
        $member = $memberId ? Member::with('subscriptions')->find($memberId) : null;
        $preselectedSubscriptionId = null;
        $preselectedAmount = null;
        $preselectedDescription = null;
        $quotaAmount = Settings::get('quota_annuale', 0);
        $causaleDefaultQuota = Settings::get('causale_default_quota', 'Quota associativa');
        $causaleDefaultDonazione = Settings::get('causale_default_donazione', 'Erogazione liberale');
        if ($subscriptionId) {
            $sub = Subscription::find($subscriptionId);
            if ($sub) {
                $preselectedSubscriptionId = (int) $sub->id;
                $preselectedAmount = (string) number_format((float) $quotaAmount, 2, '.', '');
                $preselectedDescription = $causaleDefaultQuota . ' ' . $sub->year;
            }
        }
        if ($preselectedDescription === null && $preselectedType === 'quota') {
            $preselectedDescription = $causaleDefaultQuota;
        }
        $conti = Conto::attivi()->ordered()->get(['id', 'name', 'code']);
        if ($conti->isEmpty()) {
            return redirect()->route('quote-sociali.index')->with('flash', [
                'type' => 'warning',
                'message' => 'Nessun conto tesoreria attivo. Creare almeno un conto prima di registrare incassi.',
            ]);
        }
        return Inertia::render('Incassi/Create', [
            'members' => Member::with('subscriptions')->orderBy('cognome')->orderBy('nome')->get(['id', 'nome', 'cognome']),
            'conti' => $conti,
            'preselectedType' => $preselectedType,
            'preselectedMember' => $member,
            'preselectedSubscriptionId' => $preselectedSubscriptionId,
            'preselectedAmount' => $preselectedAmount,
            'preselectedDescription' => $preselectedDescription,
            'quota_annuale' => $quotaAmount,
            'causale_default_quota' => $causaleDefaultQuota,
            'causale_default_donazione' => $causaleDefaultDonazione,
        ]);
    }

    public function store(Request $request, ReceiptService $receiptService)
    {
        $rules = [
            'type' => 'required|in:quota,donazione',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'required|date',
            'conto_id' => 'required|exists:conti,id',
            'description' => 'nullable|string|max:255',
            'issue_receipt' => 'boolean',
            'genera_prima_nota' => 'boolean',
            'confirm_anno_precedente' => 'boolean',
        ];
        if ($request->input('type') === 'quota') {
            $rules['member_id'] = 'required|exists:members,id';
        } else {
            $rules['member_id'] = 'nullable|exists:members,id';
            $rules['donor_name'] = 'nullable|string|max:255';
        }
        $request->validate($rules);

        $paidAt = Carbon::parse($request->paid_at);
        $annoPrecedente = $paidAt->year < (int) date('Y');
        $sensibile = $annoPrecedente && ($request->boolean('genera_prima_nota', true) || $request->boolean('issue_receipt'));
        if ($sensibile && ! $request->boolean('confirm_anno_precedente')) {
            return redirect()->back()->withInput()->with('flash', [
                'type' => 'confirm_anno_precedente_required',
                'message' => 'Operazioni su anni precedenti possono alterare i rendiconti già generati. Vuoi procedere?',
            ]);
        }

        $subscriptionId = $request->input('type') === 'donazione' ? null : $request->subscription_id;
        $donorName = $request->input('type') === 'donazione' ? $request->filled('donor_name') ? trim($request->donor_name) : null : null;

        $incasso = Incasso::create([
            'member_id' => $request->member_id ?: null,
            'donor_name' => $donorName,
            'subscription_id' => $subscriptionId,
            'amount' => $request->amount,
            'paid_at' => $request->paid_at,
            'conto_id' => $request->conto_id,
            'description' => $request->description,
            'genera_prima_nota' => $request->boolean('genera_prima_nota', true),
            'type' => $request->type,
        ]);

        if ($incasso->genera_prima_nota) {
            $rendicontoCode = $incasso->type === Incasso::TYPE_QUOTA
                ? RendicontoCassaSchema::CODE_QUOTA
                : RendicontoCassaSchema::CODE_DONAZIONE;
            $member = $incasso->member;
            $desc = $incasso->description;
            if ($incasso->type === Incasso::TYPE_DONAZIONE) {
                $desc = $desc ?: ($incasso->donor_name ? 'Erogazione liberale - ' . $incasso->donor_name : 'Erogazione liberale');
            } else {
                $baseDesc = $desc ?: 'Quota associativa';
                $desc = $member
                    ? $baseDesc . ' – ' . trim($member->cognome . ' ' . $member->nome)
                    : $baseDesc;
            }
            PrimaNotaEntry::create([
                'conto_id' => $incasso->conto_id,
                'rendiconto_code' => $rendicontoCode,
                'entryable_type' => Incasso::class,
                'entryable_id' => $incasso->id,
                'date' => $incasso->paid_at->toDateString(),
                'amount' => abs((float) $incasso->amount),
                'description' => $desc,
                'gestione' => 'istituzionale',
                'competenza_cassa' => true,
            ]);
        }

        if ($request->boolean('issue_receipt') && ($incasso->member_id || $incasso->donor_name)) {
            try {
                $receiptService->generateForIncasso($incasso);
            } catch (\Throwable $e) {
            }
        }

        $route = $incasso->type === Incasso::TYPE_DONAZIONE ? 'donazioni.index' : 'quote-sociali.index';
        return redirect()->route($route)->with('flash', ['type' => 'success', 'message' => 'Incasso registrato.']);
    }

    public function show(Incasso $incasso)
    {
        $incasso->load(['member', 'subscription', 'conto', 'receipt', 'primaNotaEntry']);
        return Inertia::render('Incassi/Show', ['incasso' => $incasso]);
    }
}
