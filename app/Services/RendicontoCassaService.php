<?php

namespace App\Services;

use App\Models\PrimaNotaEntry;
use Illuminate\Support\Facades\DB;

/**
 * Costruisce il rendiconto economico per cassa (Modello D) a partire dalla prima nota.
 * Usa solo voci dallo schema hardcoded (config/rendiconto_cassa.php).
 */
class RendicontoCassaService
{
    /**
     * Costruisce la struttura del rendiconto per l'anno indicato.
     * Restituisce: sezioni (con voci e importi), totale_entrate, totale_uscite, risultato_per_cassa.
     */
    public function buildRendiconto(int $anno): array
    {
        $from = "{$anno}-01-01";
        $to = "{$anno}-12-31";

        $totalsByCode = PrimaNotaEntry::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->select('rendiconto_code', DB::raw('SUM(amount) as totale'))
            ->groupBy('rendiconto_code')
            ->pluck('totale', 'rendiconto_code');

        $sezioni = [];
        $totaleEntrate = 0.0;
        $totaleUscite = 0.0;

        foreach (RendicontoCassaSchema::getAccounts() as $macro) {
            $macroName = $macro['name'] ?? '';
            $children = $macro['children'] ?? [];
            $vociSezione = [];
            $totaleEntrateSez = 0.0;
            $totaleUsciteSez = 0.0;

            foreach ($children as $child) {
                $code = $child['code'] ?? '';
                $type = $child['type'] ?? '';
                $rawImporto = (float) ($totalsByCode[$code] ?? 0);
                if ($type === 'income') {
                    $importo = max(0, $rawImporto);
                    $totaleEntrateSez += $importo;
                    $totaleEntrate += $importo;
                } else {
                    $importo = abs(min(0, $rawImporto));
                    $totaleUsciteSez += $importo;
                    $totaleUscite += $importo;
                }
                $tipo = $type === 'income' ? 'entrata' : 'uscita';
                $vociSezione[] = [
                    'codice_voce' => $code,
                    'ministerial_code' => $child['ministerial_code'] ?? $code,
                    'descrizione' => $child['name'] ?? $code,
                    'tipo' => $tipo,
                    'importo' => round($importo, 2),
                ];
            }

            $section = $macro['section'] ?? '';
            $tipoSezione = $section === 'EXPENSES' ? 'uscita' : ($section === 'INCOME' ? 'entrata' : 'misto');
            $sezioni[] = [
                'sezione' => $macroName,
                'voci' => $vociSezione,
                'totale_entrate' => round($totaleEntrateSez, 2),
                'totale_uscite' => round($totaleUsciteSez, 2),
                'tipo_sezione' => $tipoSezione,
                'area' => $macro['area'] ?? null,
            ];
        }

        $risultatoPerCassa = round($totaleEntrate - $totaleUscite, 2);

        return [
            'anno' => $anno,
            'sezioni' => $sezioni,
            'totale_entrate' => round($totaleEntrate, 2),
            'totale_uscite' => round($totaleUscite, 2),
            'risultato_per_cassa' => $risultatoPerCassa,
        ];
    }
}
