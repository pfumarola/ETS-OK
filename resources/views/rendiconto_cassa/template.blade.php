@php
    $sezioni = $rendiconto['sezioni'] ?? [];
    $anno = $rendiconto['anno'] ?? '';
    $annoPrec = $rendiconto['anno_precedente'] ?? (($rendiconto['anno'] ?? 0) - 1);
    $totaleUsciteT = 0.0;
    $totaleUsciteT1 = 0.0;
    $totaleEntrateT = 0.0;
    $totaleEntrateT1 = 0.0;

    $areeLettere = ['A', 'B', 'C', 'D', 'E'];
    $blocchiAree = [];
    $sezioniByArea = [];
    $sezioniMiste = [];
    foreach ($sezioni as $s) {
        $area = $s['area'] ?? null;
        $tipo = $s['tipo_sezione'] ?? '';
        if (in_array($area, $areeLettere, true)) {
            if (!isset($sezioniByArea[$area])) {
                $sezioniByArea[$area] = ['uscite' => null, 'entrate' => null];
            }
            if ($tipo === 'uscita') {
                $sezioniByArea[$area]['uscite'] = $s;
            } elseif ($tipo === 'entrata') {
                $sezioniByArea[$area]['entrate'] = $s;
            }
        } elseif ($tipo === 'misto') {
            $sezioniMiste[] = $s;
        }
    }
    foreach ($areeLettere as $area) {
        $uscite = $sezioniByArea[$area]['uscite'] ?? null;
        $entrate = $sezioniByArea[$area]['entrate'] ?? null;
        if ($uscite || $entrate) {
            $vociU = $uscite ? ($uscite['voci'] ?? []) : [];
            $vociE = $entrate ? ($entrate['voci'] ?? []) : [];
            $totU = $uscite ? (float)($uscite['totale_uscite'] ?? 0) : 0;
            $totU1 = $uscite ? array_sum(array_map(function ($v) { return (float)($v['importo_anno_precedente'] ?? 0); }, $vociU)) : 0;
            $totE = $entrate ? (float)($entrate['totale_entrate'] ?? 0) : 0;
            $totE1 = $entrate ? array_sum(array_map(function ($v) { return (float)($v['importo_anno_precedente'] ?? 0); }, $vociE)) : 0;
            $totaleUsciteT += $totU;
            $totaleUsciteT1 += $totU1;
            $totaleEntrateT += $totE;
            $totaleEntrateT1 += $totE1;
            $n = max(count($vociU), count($vociE), 1);
            $righeDettaglio = [];
            for ($i = 0; $i < $n; $i++) {
                $vu = $vociU[$i] ?? null;
                $ve = $vociE[$i] ?? null;
                $righeDettaglio[] = [
                    'desc_u' => $vu ? ($vu['ministerial_code'] ?? $vu['codice_voce']) . ' – ' . ($vu['descrizione'] ?? '') : '',
                    't_u' => $vu ? (float)($vu['importo'] ?? 0) : null,
                    't1_u' => $vu ? (float)($vu['importo_anno_precedente'] ?? 0) : null,
                    'desc_e' => $ve ? ($ve['ministerial_code'] ?? $ve['codice_voce']) . ' – ' . ($ve['descrizione'] ?? '') : '',
                    't_e' => $ve ? (float)($ve['importo'] ?? 0) : null,
                    't1_e' => $ve ? (float)($ve['importo_anno_precedente'] ?? 0) : null,
                ];
            }
            $avanzoT = round($totE - $totU, 2);
            $avanzoT1 = round($totE1 - $totU1, 2);
            $blocchiAree[] = [
                'area' => $area,
                'titolo_uscite' => $uscite ? $uscite['sezione'] : '',
                'titolo_entrate' => $entrate ? $entrate['sezione'] : '',
                'righe' => $righeDettaglio,
                'tot_u' => round($totU, 2),
                'tot_u1' => round($totU1, 2),
                'tot_e' => round($totE, 2),
                'tot_e1' => round($totE1, 2),
                'avanzo_t' => $avanzoT,
                'avanzo_t1' => $avanzoT1,
                'mostra_avanzo' => in_array($area, ['A', 'B', 'C', 'D'], true),
            ];
        }
    }
    foreach ($sezioniMiste as $m) {
        $voci = $m['voci'] ?? [];
        $vociU = array_values(array_filter($voci, function ($v) { return ($v['tipo'] ?? '') === 'uscita'; }));
        $vociE = array_values(array_filter($voci, function ($v) { return ($v['tipo'] ?? '') === 'entrata'; }));
        if (count($vociU) === 0 && count($vociE) === 0) {
            continue;
        }
        $totU = array_sum(array_column($vociU, 'importo'));
        $totU1 = array_sum(array_map(function ($v) { return (float)($v['importo_anno_precedente'] ?? 0); }, $vociU));
        $totE = array_sum(array_column($vociE, 'importo'));
        $totE1 = array_sum(array_map(function ($v) { return (float)($v['importo_anno_precedente'] ?? 0); }, $vociE));
        $totaleUsciteT += $totU;
        $totaleUsciteT1 += $totU1;
        $totaleEntrateT += $totE;
        $totaleEntrateT1 += $totE1;
        $n = max(count($vociU), count($vociE), 1);
        $righeDettaglio = [];
        for ($i = 0; $i < $n; $i++) {
            $vu = $vociU[$i] ?? null;
            $ve = $vociE[$i] ?? null;
            $righeDettaglio[] = [
                'desc_u' => $vu ? ($vu['ministerial_code'] ?? $vu['codice_voce']) . ' – ' . ($vu['descrizione'] ?? '') : '',
                't_u' => $vu ? (float)($vu['importo'] ?? 0) : null,
                't1_u' => $vu ? (float)($vu['importo_anno_precedente'] ?? 0) : null,
                'desc_e' => $ve ? ($ve['ministerial_code'] ?? $ve['codice_voce']) . ' – ' . ($ve['descrizione'] ?? '') : '',
                't_e' => $ve ? (float)($ve['importo'] ?? 0) : null,
                't1_e' => $ve ? (float)($ve['importo_anno_precedente'] ?? 0) : null,
            ];
        }
        $nome = $m['sezione'] ?? '';
        $blocchiAree[] = [
            'area' => 'INV',
            'titolo_uscite' => $nome,
            'titolo_entrate' => $nome,
            'righe' => $righeDettaglio,
            'tot_u' => round($totU, 2),
            'tot_u1' => round($totU1, 2),
            'tot_e' => round($totE, 2),
            'tot_e1' => round($totE1, 2),
            'avanzo_t' => null,
            'avanzo_t1' => null,
            'mostra_avanzo' => false,
        ];
    }

    $totaleUsciteT = round($totaleUsciteT, 2);
    $totaleUsciteT1 = round($totaleUsciteT1, 2);
    $totaleEntrateT = round($totaleEntrateT, 2);
    $totaleEntrateT1 = round($totaleEntrateT1, 2);
    $risultatoT = (float)($rendiconto['risultato_per_cassa'] ?? 0);
    $risultatoT1 = round($totaleEntrateT1 - $totaleUsciteT1, 2);
    $righeSintesi = [
        ['etichetta' => "Avanzo/disavanzo d'esercizio prima delle imposte", 'es_t' => $risultatoT, 'es_t1' => $risultatoT1],
        ['etichetta' => 'Imposte', 'es_t' => null, 'es_t1' => null],
        ['etichetta' => "Avanzo/disavanzo d'esercizio prima di investimenti e disinvestimenti patrimoniali, e finanziamenti", 'es_t' => $risultatoT, 'es_t1' => $risultatoT1],
        ['etichetta' => 'Avanzo/disavanzo complessivo', 'es_t' => $risultatoT, 'es_t1' => $risultatoT1],
    ];
    $fmt = function ($n) { return $n !== null && $n !== '' ? number_format((float)$n, 2, ',', '.') : '—'; };
@endphp
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Rendiconto per cassa {{ $anno }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; padding: 105px 20px 20px 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 12px; color: #333; margin-top: 4px; }
        .tabella-rendiconto { width: 100%; border-collapse: collapse; margin: 6px 0; }
        .tabella-rendiconto th, .tabella-rendiconto td { padding: 5px 6px; border: 1px solid #ddd; }
        .tabella-rendiconto th { background: #f5f5f5; font-weight: bold; text-align: left; }
        .tabella-rendiconto td.num { text-align: right; }
        .tabella-rendiconto .totale-row { font-weight: bold; background: #f9f9f9; }
        .risultato { font-size: 14px; font-weight: bold; margin-top: 16px; padding: 10px; border: 1px solid #333; }
        .footer { margin-top: 24px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="letterhead-fixed">@include('pdf.letterhead', $letterhead ?? [])</div>
    <div class="header">
        <div class="title">Rendiconto economico per cassa – Anno {{ $anno }}</div>
        <div class="subtitle">Modello D – DM 5 marzo 2020 (criterio di cassa).</div>
    </div>

    <table class="tabella-rendiconto">
        <thead>
            <tr>
                <th colspan="3" style="text-align: center;">USCITE</th>
                <th colspan="3" style="text-align: center;">ENTRATE</th>
            </tr>
            <tr>
                <th style="width: 32%;">Voce</th>
                <th style="width: 9%; text-align: right;">{{ $anno }}</th>
                <th style="width: 9%; text-align: right;">{{ $annoPrec }}</th>
                <th style="width: 32%;">Voce</th>
                <th style="width: 9%; text-align: right;">{{ $anno }}</th>
                <th style="width: 9%; text-align: right;">{{ $annoPrec }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($blocchiAree as $blocco)
                <tr>
                    <td><strong>{{ $blocco['titolo_uscite'] ? (in_array($blocco['area'], $areeLettere, true) ? $blocco['area'] . ') ' : '') . $blocco['titolo_uscite'] : '—' }}</strong></td>
                    <td class="num"></td>
                    <td class="num"></td>
                    <td><strong>{{ $blocco['titolo_entrate'] ? (in_array($blocco['area'], $areeLettere, true) ? $blocco['area'] . ') ' : '') . $blocco['titolo_entrate'] : '—' }}</strong></td>
                    <td class="num"></td>
                    <td class="num"></td>
                </tr>
                @foreach($blocco['righe'] as $r)
                <tr>
                    <td>{{ $r['desc_u'] ?: '—' }}</td>
                    <td class="num">{{ $r['t_u'] !== null ? $fmt($r['t_u']) : '—' }}</td>
                    <td class="num">{{ $r['t1_u'] !== null ? $fmt($r['t1_u']) : '—' }}</td>
                    <td>{{ $r['desc_e'] ?: '—' }}</td>
                    <td class="num">{{ $r['t_e'] !== null ? $fmt($r['t_e']) : '—' }}</td>
                    <td class="num">{{ $r['t1_e'] !== null ? $fmt($r['t1_e']) : '—' }}</td>
                </tr>
                @endforeach
                <tr class="totale-row">
                    <td>Totale</td>
                    <td class="num">{{ $fmt($blocco['tot_u']) }}</td>
                    <td class="num">{{ $fmt($blocco['tot_u1']) }}</td>
                    <td>Totale</td>
                    <td class="num">{{ $fmt($blocco['tot_e']) }}</td>
                    <td class="num">{{ $fmt($blocco['tot_e1']) }}</td>
                </tr>
                @if($blocco['mostra_avanzo'])
                <tr>
                    <td></td>
                    <td class="num"></td>
                    <td class="num"></td>
                    <td><strong>Avanzo/disavanzo attività {{ $blocco['area'] === 'A' ? 'di interesse generale' : ($blocco['area'] === 'B' ? 'diverse' : ($blocco['area'] === 'C' ? 'di raccolta fondi' : 'finanziarie e patrimoniali')) }}</strong></td>
                    <td class="num">{{ $blocco['avanzo_t'] !== null ? $fmt($blocco['avanzo_t']) : '—' }}</td>
                    <td class="num">{{ $blocco['avanzo_t1'] !== null ? $fmt($blocco['avanzo_t1']) : '—' }}</td>
                </tr>
                @endif
            @endforeach
            <tr class="totale-row">
                <td></td>
                <td class="num"></td>
                <td class="num"></td>
                <td><strong>Totale entrate della gestione</strong></td>
                <td class="num">{{ $fmt($totaleEntrateT) }}</td>
                <td class="num">{{ $fmt($totaleEntrateT1) }}</td>
            </tr>
            <tr class="totale-row">
                <td><strong>Totale uscite della gestione</strong></td>
                <td class="num">{{ $fmt($totaleUsciteT) }}</td>
                <td class="num">{{ $fmt($totaleUsciteT1) }}</td>
                <td></td>
                <td class="num"></td>
                <td class="num"></td>
            </tr>
            @foreach($righeSintesi as $rs)
            <tr>
                <td></td>
                <td class="num"></td>
                <td class="num"></td>
                <td><strong>{{ $rs['etichetta'] }}</strong></td>
                <td class="num">{{ $rs['es_t'] !== null ? $fmt($rs['es_t']) : '—' }}</td>
                <td class="num">{{ $rs['es_t1'] !== null ? $fmt($rs['es_t1']) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="risultato">
        Risultato per cassa (entrate − uscite): € {{ number_format((float)($rendiconto['risultato_per_cassa'] ?? 0), 2, ',', '.') }}
    </div>

    <div class="footer">
        Documento generato il {{ $rendiconto['data_generazione'] ?? now()->format('d/m/Y H:i') }}. Redatto con criterio di cassa – Modello D DM 5 marzo 2020 (GU n. 96 del 18-04-2020).
    </div>
    @include('pdf.footer-pagination')
</body>
</html>
