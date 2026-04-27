<?php

return [
    'templates' => [
        'assemblea' => [
            'body' => <<<'HTML'
<p>Gentile socio/a,</p>
<p>con la presente si convoca {{tipo_label}} dell'associazione {{nome_associazione}}.</p>
<p><strong>Data:</strong> {{data}}<br><strong>Ora:</strong> {{ora}}<br><strong>Luogo:</strong> {{luogo}}</p>
<p><strong>Ordine del giorno</strong></p>
<p>{{ordine_del_giorno}}</p>
<p>Cordiali saluti.</p>
HTML,
        ],
        'consiglio_direttivo' => [
            'body' => <<<'HTML'
<p>Gentile componente del Consiglio direttivo,</p>
<p>con la presente si convoca {{tipo_label}} dell'associazione {{nome_associazione}}.</p>
<p><strong>Data:</strong> {{data}}<br><strong>Ora:</strong> {{ora}}<br><strong>Luogo:</strong> {{luogo}}</p>
<p><strong>Ordine del giorno</strong></p>
<p>{{ordine_del_giorno}}</p>
<p>Cordiali saluti.</p>
HTML,
        ],
    ],
];
