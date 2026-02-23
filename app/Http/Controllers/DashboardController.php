<?php

namespace App\Http\Controllers;

use App\Models\Elezione;
use App\Models\Event;
use App\Models\ExpenseRefund;
use App\Models\Incasso;
use App\Models\Member;
use App\Models\Organo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Utente senza alcun ruolo: nessun dato sensibile
        if (! $user->roles()->exists()) {
            return Inertia::render('Dashboard', [
                'forSocio' => false,
                'noRole' => true,
            ]);
        }

        // Socio con profilo collegato e stato attivo: dashboard semplificata (solo area personale)
        if ($user->hasRole('socio') && $user->member && ! $user->hasRole('admin', 'segreteria', 'contabile')) {
            $member = $user->member;
            if ($member->stato !== 'attivo') {
                return Inertia::render('Dashboard', [
                    'forSocio' => false,
                    'memberStatusBlocked' => true,
                ]);
            }
            $inRegolaConQuota = $member->isInRegolaConQuota();
            $today = now()->startOfDay();
            $votazioniAperte = Elezione::where('stato', Elezione::STATO_APERTA)
                ->where('data_elezione', '=', $today->toDateString())
                ->orderBy('data_elezione')
                ->get(['id', 'titolo']);

            return Inertia::render('Dashboard', [
                'forSocio' => true,
                'member' => [
                    'id' => $member->id,
                    'full_name' => $member->full_name,
                    'in_regola_con_quota' => $inRegolaConQuota,
                ],
                'votazioniAperte' => $votazioniAperte,
            ]);
        }

        $today = now()->startOfDay();
        $scadenzaEntro = $today->copy()->addDays(90);

        // Organi con mandato in scadenza: scadenza calcolata da ultime elezioni o data costituzione
        $organiInScadenza = Organo::query()
            ->whereNotNull('durata_mesi')
            ->where('durata_mesi', '>', 0)
            ->orderBy('nome')
            ->get()
            ->filter(function (Organo $organo) use ($today, $scadenzaEntro) {
                $s = $organo->mandatoScadenza();
                return $s && $s->gte($today) && $s->lte($scadenzaEntro);
            })
            ->take(10)
            ->values();
        foreach ($organiInScadenza as $o) {
            $o->setAttribute('mandato_da', $o->dataInizioMandato()?->toDateString());
            $o->setAttribute('mandato_scadenza', $o->mandatoScadenza()?->toDateString());
        }
        $organiInScadenza->load(['caricheSociali' => function ($q) {
            $q->orderBy('ordine')->with(['incarichi' => function ($q2) {
                $q2->with('member:id,cognome,nome');
            }]);
        }]);

        $canApproveRefunds = $user->hasRole('admin') || $user->hasRole('contabile');
        $refundsPendingApproval = $canApproveRefunds
            ? ExpenseRefund::where('status', 'richiesta')
                ->orderByDesc('refund_date')
                ->take(10)
                ->get(['id', 'refund_date', 'total', 'member_id'])
                ->load('member:id,cognome,nome')
            : [];

        $stats = [
            'forSocio' => false,
            'members_count' => Member::nonCessati()->inRegolaConQuota()->count(),
            'payments_this_month' => Incasso::whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('amount'),
            'members_not_in_regola' => Member::nonCessati()->count() - Member::nonCessati()->inRegolaConQuota()->count(),
            'upcoming_events' => Event::where('start_at', '>=', now())->orderBy('start_at')->take(5)->get(['id', 'title', 'start_at']),
            'recent_incassi' => Incasso::with('member:id,cognome,nome')->orderByDesc('paid_at')->take(5)->get(),
            'organiInScadenza' => $organiInScadenza,
            'refunds_pending_approval' => $refundsPendingApproval,
        ];

        return Inertia::render('Dashboard', $stats);
    }
}
