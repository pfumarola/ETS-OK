<?php

namespace App\Http\Controllers;

use App\Models\Incasso;
use App\Models\Member;
use App\Models\PaymentMethod;
use App\Models\PrimaNotaEntry;
use App\Models\Settings;
use App\Models\Subscription;
use App\Services\ReceiptService;
use App\Services\RendicontoCassaSchema;
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

    public function index(Request $request)
    {
        $query = Incasso::with(['member', 'subscription', 'paymentMethod', 'receipt']);

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('paid_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('paid_at', '<=', $request->to);
        }
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $incassi = $query->orderByDesc('paid_at')->paginate(20)->withQueryString();
        return Inertia::render('Incassi/Index', [
            'incassi' => $incassi,
            'filters' => $request->only('member_id', 'from', 'to', 'type'),
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
        return Inertia::render('Incassi/Create', [
            'members' => Member::with('subscriptions')->orderBy('cognome')->orderBy('nome')->get(['id', 'nome', 'cognome']),
            'paymentMethods' => PaymentMethod::orderBy('name')->get(),
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
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'description' => 'nullable|string|max:255',
            'issue_receipt' => 'boolean',
            'genera_prima_nota' => 'boolean',
        ];
        if ($request->input('type') === 'quota') {
            $rules['member_id'] = 'required|exists:members,id';
        } else {
            $rules['member_id'] = 'nullable|exists:members,id';
            $rules['donor_name'] = 'nullable|string|max:255';
        }
        $request->validate($rules);

        $subscriptionId = $request->input('type') === 'donazione' ? null : $request->subscription_id;
        $donorName = $request->input('type') === 'donazione' ? $request->filled('donor_name') ? trim($request->donor_name) : null : null;

        $incasso = Incasso::create([
            'member_id' => $request->member_id ?: null,
            'donor_name' => $donorName,
            'subscription_id' => $subscriptionId,
            'amount' => $request->amount,
            'paid_at' => $request->paid_at,
            'payment_method_id' => $request->payment_method_id,
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
                $desc = $desc ?: ($member ? 'Quota ' . trim($member->cognome . ' ' . $member->nome) : 'Quota associativa');
            }
            PrimaNotaEntry::create([
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

        return redirect()->route('incassi.index')->with('flash', ['type' => 'success', 'message' => 'Incasso registrato.']);
    }

    public function show(Incasso $incasso)
    {
        $incasso->load(['member', 'subscription', 'paymentMethod', 'receipt', 'primaNotaEntry']);
        return Inertia::render('Incassi/Show', ['incasso' => $incasso]);
    }
}
