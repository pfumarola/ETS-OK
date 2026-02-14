<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Ricevuta {{ $receipt->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; padding: 20px; }
        .header { text-align: center; margin-bottom: 24px; }
        .receipt-title { font-size: 18px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .amount { font-size: 16px; font-weight: bold; }
        .footer { margin-top: 32px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($logo_data_uri))
            <img src="{{ $logo_data_uri }}" alt="Logo" style="max-height: 48px; margin-bottom: 8px;" />
        @endif
        <div class="receipt-title">RICEVUTA N. {{ $receipt->number }}</div>
        <div>{{ $nome_associazione ?? 'Associazione - Ente del Terzo Settore' }}</div>
    </div>

    <p>Ricevuta liberale / Quota associativa</p>

    <table>
        <tr><th>Destinatario</th><td>{{ $member->cognome }} {{ $member->nome }}</td></tr>
        @if($member->codice_fiscale)
        <tr><th>Codice fiscale</th><td>{{ $member->codice_fiscale }}</td></tr>
        @endif
        @if($member->indirizzo)
        <tr><th>Indirizzo</th><td>{{ $member->indirizzo }}</td></tr>
        @endif
        <tr><th>Causale</th><td>{{ $causale }}</td></tr>
        <tr><th>Importo</th><td class="amount">€ {{ number_format((float) $amount, 2, ',', '.') }}</td></tr>
        <tr><th>Data</th><td>{{ \Carbon\Carbon::parse($issued_at)->format('d/m/Y') }}</td></tr>
    </table>

    <div class="footer">
        @if(!empty($indirizzo_associazione))
            <div>{{ $indirizzo_associazione }}</div>
        @endif
        @if(!empty($codice_fiscale_associazione))
            <div>Codice fiscale: {{ $codice_fiscale_associazione }}</div>
        @endif
        @if(!empty($partita_iva_associazione))
            <div>P.IVA: {{ $partita_iva_associazione }}</div>
        @endif
        <div style="margin-top: 8px">Documento generato il {{ now()->format('d/m/Y H:i') }}. Valido come ricevuta per erogazione liberale / quota associativa.</div>
    </div>
</body>
</html>
