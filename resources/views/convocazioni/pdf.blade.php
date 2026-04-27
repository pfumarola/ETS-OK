<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Convocazione - {{ $convocazione->titolo ?: $convocazione->tipo_label }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; padding: 105px 24px 24px 24px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        .meta { font-size: 11px; color: #333; margin-top: 8px; }
        .content { margin-top: 20px; }
        .content p { margin: 0 0 8px 0; }
        .content ul, .content ol { margin: 8px 0; padding-left: 24px; }
        .section-title { font-size: 12px; font-weight: bold; margin: 14px 0 8px 0; }
        .footer { margin-top: 28px; font-size: 9px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="letterhead-fixed">@include('pdf.letterhead', $letterhead ?? [])</div>
    <div class="header">
        <div class="title">{{ $convocazione->titolo ?: ('Convocazione ' . ($convocazione->tipo_label ?? $convocazione->tipo)) }}</div>
        <div class="meta">
            {{ $convocazione->tipo_label ?? $convocazione->tipo }}
            @if($convocazione->scheduled_at)
                - {{ $convocazione->scheduled_at->format('d/m/Y H:i') }}
            @endif
            - {{ $convocazione->luogo }}
        </div>
    </div>

    <div class="content">
        <div class="section-title">Ordine del giorno</div>
        {!! $convocazione->ordine_del_giorno !!}

        @if(!empty($convocazione->testo_email))
            <div class="section-title">Testo convocazione</div>
            {!! $convocazione->testo_email !!}
        @endif
    </div>

    <div class="footer">
        Documento generato il {{ now()->format('d/m/Y H:i') }}.
    </div>
    @include('pdf.footer-pagination')
</body>
</html>
