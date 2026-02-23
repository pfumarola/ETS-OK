<?php

return [
    'types' => [
        'invito_ammissione' => [
            'label' => 'Invito domanda ammissione',
            'default_subject' => '[{{appName}}] Invito a presentare domanda di ammissione',
            'default_body' => '<p>Buongiorno,</p><p>Sei stato/a invitato/a a presentare domanda di ammissione come socio presso {{appName}}.</p><p>Clicca sul link qui sotto per compilare il modulo (il link è personale e a uso singolo):</p><p><a href="{{link}}">{{link}}</a></p><p>Il link è valido per {{expiry_days}} giorni. Non condividerlo con altri.</p><p>Cordiali saluti,<br>{{appName}}</p>',
            'placeholders' => [
                'link' => 'Link per compilare la domanda (a uso singolo)',
                'expiry_days' => 'Giorni di validità del link',
                'appName' => 'Nome associazione',
            ],
        ],
        'ricevuta' => [
            'label' => 'Ricevuta inviata per email',
            'default_subject' => '[{{appName}}] Ricevuta n. {{receipt_number}}',
            'default_body' => '<p>Buongiorno,</p><p>In allegato trovi la ricevuta n. <strong>{{receipt_number}}</strong> emessa il {{receipt_issued_at}}.</p><p>Saluti,<br>{{appName}}</p>',
            'placeholders' => [
                'receipt_number' => 'Numero ricevuta',
                'receipt_issued_at' => 'Data emissione (formattata)',
                'appName' => 'Nome associazione',
                'receipt_amount' => 'Importo (opzionale)',
                'recipient_name' => 'Nome destinatario (opzionale)',
            ],
        ],
    ],
];
