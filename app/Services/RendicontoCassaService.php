<?php

namespace App\Services;

use App\Models\Conto;
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

        $contiSaldi = $this->buildContiSaldi($anno);

        return [
            'anno' => $anno,
            'sezioni' => $sezioni,
            'totale_entrate' => round($totaleEntrate, 2),
            'totale_uscite' => round($totaleUscite, 2),
            'risultato_per_cassa' => $risultatoPerCassa,
            'conti_saldi' => $contiSaldi,
        ];
    }

    /**
     * Saldi per conto tesoreria (CASSA E BANCA): anno corrente e anno precedente.
     * Restituisce array di ['nome' => string, 'tipo' => string, 'saldo_anno' => float, 'saldo_anno_precedente' => float].
     */
    public function buildContiSaldi(int $anno): array
    {
        $annoPrec = $anno - 1;
        $from = "{$anno}-01-01";
        $to = "{$anno}-12-31";
        $from1 = "{$annoPrec}-01-01";
        $to1 = "{$annoPrec}-12-31";

        $saldiAnno = PrimaNotaEntry::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->select('conto_id', DB::raw('SUM(amount) as saldo'))
            ->groupBy('conto_id')
            ->pluck('saldo', 'conto_id');

        $saldiAnnoPrec = [];
        if ($annoPrec >= 2000 && $annoPrec <= 2100) {
            $saldiAnnoPrec = PrimaNotaEntry::query()
                ->whereDate('date', '>=', $from1)
                ->whereDate('date', '<=', $to1)
                ->select('conto_id', DB::raw('SUM(amount) as saldo'))
                ->groupBy('conto_id')
                ->pluck('saldo', 'conto_id');
        }

        $conti = Conto::attivi()->ordered()->get(['id', 'name', 'type']);
        $out = [];
        foreach ($conti as $c) {
            $out[] = [
                'nome' => $c->name,
                'tipo' => $c->type,
                'saldo_anno' => round((float) ($saldiAnno[$c->id] ?? 0), 2),
                'saldo_anno_precedente' => round((float) ($saldiAnnoPrec[$c->id] ?? 0), 2),
            ];
        }
        return $out;
    }
}
