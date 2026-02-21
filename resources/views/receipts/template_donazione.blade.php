<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Ricevuta donazione {{ $receipt->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; padding: 105px 20px 20px 20px; line-height: 1.35; }
        .letterhead-fixed { position: fixed; top: 0; left: 0; right: 0; height: 68px; z-index: 1; }
        .receipt-header { display: table; width: 100%; margin-bottom: 20px; }
        .receipt-header-left { display: table-cell; vertical-align: top; width: 50%; }
        .receipt-header-right { display: table-cell; vertical-align: top; text-align: right; }
        .receipt-title { font-size: 18px; font-weight: bold; text-align: center; margin: 16px 0 20px 0; }
        .section { margin: 14px 0; }
        .section-label { font-weight: bold; margin-bottom: 4px; }
        .checkbox-list { margin: 8px 0; }
        .checkbox-item { margin: 3px 0; }
        .legal-block { margin: 14px 0; font-size: 10px; text-align: justify; }
        .legal-block ul { margin: 6px 0 6px 18px; padding: 0; }
        .legal-block li { margin: 4px 0; }
        .donor-fields { margin: 8px 0; }
        .donor-fields p { margin: 4px 0; }
        .donor-fields .label { display: inline-block; min-width: 80px; }
        .donor-fields .value { border-bottom: 1px solid #333; min-width: 200px; display: inline-block; }
        .amount { font-size: 14px; font-weight: bold; }
        .signature-area { margin-top: 32px; text-align: right; }
        .signature-label { font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="letterhead-fixed">@include('pdf.letterhead')</div>

    <div class="receipt-header">
        <div class="receipt-header-left">Ric. N. {{ $receipt->number }}</div>
        <div class="receipt-header-right">Luogo {{ $luogo_emissione ?? '________________' }}, {{ \Carbon\Carbon::parse($issued_at)->format('d/m/Y') }}</div>
    </div>

    <div class="receipt-title">RICEVUTA DONAZIONE</div>

    <div class="section">
        <p>L'associazione <strong>{{ $nome_associazione }}</strong> con sede in {{ $indirizzo_associazione ?: '________________' }}, C.F. {{ $codice_fiscale_associazione ?: '________________' }}, nella persona del suo legale rappresentante {{ $legale_rappresentante ?: '________________' }} dichiara di aver ricevuto la seguente donazione in data {{ \Carbon\Carbon::parse($issued_at)->format('d/m/Y') }} Euro <span class="amount">{{ number_format((float) $amount, 2, ',', '.') }}</span></p>
    </div>

    <div class="section">
        <div class="section-label">tramite</div>
        <div class="checkbox-list">
            <div class="checkbox-item">☐ bonifico bancario</div>
            <div class="checkbox-item">☐ bonifico postale</div>
            <div class="checkbox-item">☐ versamento con bollettino postale</div>
            <div class="checkbox-item">☐ assegno circolare</div>
            <div class="checkbox-item">☐ assegno bancario non trasferibile</div>
            <div class="checkbox-item">☐ carta di credito</div>
            <div class="checkbox-item">☐ carta di debito</div>
            <div class="checkbox-item">☐ altro</div>
        </div>
    </div>

    <div class="section">
        <div class="section-label">da:</div>
        <div class="donor-fields">
            <p><span class="label">Nominativo</span> <span class="value">{{ $member ? ($member->cognome . ' ' . $member->nome) : ($recipient_name ?: '________________') }}</span></p>
            <p><span class="label">Indirizzo</span> <span class="value">{{ $member && $member->indirizzo ? $member->indirizzo : '________________' }}</span></p>
            <p><span class="label">CAP</span> <span class="value">________________</span> <span class="label" style="margin-left: 20px;">Città</span> <span class="value">________________</span></p>
            <p><span class="label">C.F.</span> <span class="value">{{ $member && $member->codice_fiscale ? $member->codice_fiscale : '________________' }}</span></p>
        </div>
    </div>

    <div class="section">
        <p>L'associazione <strong>{{ $nome_associazione }}</strong> è ente del Terzo settore in quanto iscritta al Runts in data {{ $data_iscrizione_runts ?: '________________' }}.</p>
    </div>

    <div class="legal-block">
        <p><strong>In quanto Ente del Terzo Settore si applicano le disposizioni dell'art. 83 del D.Lgs. n. 117/2017.</strong> Ai sensi di tali disposizioni, le persone fisiche possono alternativamente:</p>
        <ul>
            <li>detrarre le erogazioni effettuate a favore della nostra Associazione per un importo pari al {{ $ets_è_odv ? '35%' : '30%' }}{{ $ets_è_odv ? '' : ' (35% se ODV)' }} fino al massimo di € 30.000;</li>
            <li>dedurre le erogazioni effettuate a favore della nostra associazione nei limiti del 10% del reddito complessivo dichiarato*.</li>
        </ul>
        <p>Le persone giuridiche possono:</p>
        <ul>
            <li>dedurre le erogazioni effettuate a favore della nostra associazione nei limiti del 10% del reddito complessivo dichiarato*.</li>
        </ul>
        <p>L'associazione nel rispetto dell'art. 8 D.lgs 117/17 utilizzerà i fondi ricevuti per lo svolgimento di attività statutaria ai fini dell'esclusivo perseguimento di finalità civiche, solidaristiche e di utilità sociale.</p>
        <p style="font-size: 9px; margin-top: 10px;">* Qualora detto importo sia di ammontare superiore al reddito complessivo dichiarato, diminuito di tutte le deduzioni, l'eccedenza può essere computata in aumento dell'importo deducibile dal reddito complessivo dei periodi di imposta successivi, ma non oltre il quarto, fino a concorrenza del suo ammontare.</p>
    </div>

    <div class="signature-area">
        <div class="signature-label">Timbro e firma</div>
        <div style="margin-top: 40px; border-bottom: 1px solid #333; width: 200px; margin-left: auto;"></div>
    </div>
</body>
</html>
