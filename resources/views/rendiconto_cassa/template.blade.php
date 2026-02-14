<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Rendiconto per cassa {{ $rendiconto['anno'] ?? '' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 12px; color: #333; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { padding: 6px 8px; border: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: bold; text-align: left; }
        td.num { text-align: right; }
        .sezione-head { background: #e8e8e8; font-weight: bold; padding: 8px; }
        .totale-row { font-weight: bold; background: #f9f9f9; }
        .risultato { font-size: 14px; font-weight: bold; margin-top: 16px; padding: 10px; border: 1px solid #333; }
        .footer { margin-top: 24px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($logo_data_uri))
            <img src="{{ $logo_data_uri }}" alt="Logo" style="max-height: 48px; margin-bottom: 8px;" />
        @endif
        <div class="title">{{ $nome_associazione ?? 'Associazione - Ente del Terzo Settore' }}</div>
        <div class="subtitle">Rendiconto economico per cassa – Anno {{ $rendiconto['anno'] ?? '' }}</div>
        <div class="subtitle">Modello D – DM 5 marzo 2020 (criterio di cassa)</div>
    </div>

    @foreach($rendiconto['sezioni'] ?? [] as $sezione)
        <div class="sezione-head">Sezione {{ $sezione['sezione'] }}</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Codice</th>
                    <th>Voce</th>
                    <th style="width: 18%; text-align: right;">Importo (€)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sezione['voci'] ?? [] as $voce)
                    <tr>
                        <td>{{ $voce['codice_voce'] }}</td>
                        <td>{{ $voce['descrizione'] }}</td>
                        <td class="num">{{ number_format((float) $voce['importo'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if(!empty($sezione['totale_entrate']) && (float) $sezione['totale_entrate'] > 0)
            <div style="text-align: right; margin-bottom: 8px;">Totale entrate sezione: € {{ number_format((float) $sezione['totale_entrate'], 2, ',', '.') }}</div>
        @endif
        @if(!empty($sezione['totale_uscite']) && (float) $sezione['totale_uscite'] > 0)
            <div style="text-align: right; margin-bottom: 12px;">Totale uscite sezione: € {{ number_format((float) $sezione['totale_uscite'], 2, ',', '.') }}</div>
        @endif
    @endforeach

    <table class="totale-row">
        <tr>
            <td style="border: none; padding-top: 12px;"><strong>Totale entrate</strong></td>
            <td class="num" style="border: none; padding-top: 12px;">€ {{ number_format((float) ($rendiconto['totale_entrate'] ?? 0), 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Totale uscite</strong></td>
            <td class="num" style="border: none;">€ {{ number_format((float) ($rendiconto['totale_uscite'] ?? 0), 2, ',', '.') }}</td>
        </tr>
    </table>

    <div class="risultato">
        Risultato per cassa (entrate − uscite): € {{ number_format((float) ($rendiconto['risultato_per_cassa'] ?? 0), 2, ',', '.') }}
    </div>

    <div class="footer">
        Documento generato il {{ now()->format('d/m/Y H:i') }}. Redatto con criterio di cassa – Modello D DM 5 marzo 2020 (GU n. 96 del 18-04-2020).
    </div>
</body>
</html>
