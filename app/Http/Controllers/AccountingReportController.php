<?php

namespace App\Http\Controllers;

use App\Models\PrimaNotaEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Report contabilità: prima nota per periodo, totali. Export CSV semplice.
 */
class AccountingReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,contabile');
    }

    public function index(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $entries = PrimaNotaEntry::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $totalEntrate = $entries->where('amount', '>', 0)->sum('amount');
        $totalUscite = abs($entries->where('amount', '<', 0)->sum('amount'));

        return Inertia::render('Reports/Accounting', [
            'entries' => $entries,
            'totalEntrate' => $totalEntrate,
            'totalUscite' => $totalUscite,
            'filters' => ['from' => $from, 'to' => $to],
        ]);
    }

    /**
     * Export CSV prima nota per periodo.
     */
    public function export(Request $request): StreamedResponse
    {
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $entries = PrimaNotaEntry::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->orderBy('date')
            ->get();

        $filename = 'prima_nota_' . $from . '_' . $to . '.csv';

        return response()->streamDownload(function () use ($entries) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Data', 'Conto', 'Descrizione', 'Importo']);
            foreach ($entries as $e) {
                fputcsv($out, [
                    $e->date->format('d/m/Y'),
                    $e->rendiconto_label,
                    $e->description ?? '',
                    $e->amount,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
