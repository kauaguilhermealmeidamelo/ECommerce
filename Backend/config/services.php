<?php

return [
    'mercadopago' => [
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'melhorenvio' => [
        // Fallback só pra ambiente de desenvolvimento/testes. Em produção,
        // cada loja usa o próprio token, salvo criptografado em
        // configuracoes_entrega.token_melhor_envio (ver EntregasView no admin).
        'token' => env('MELHORENVIO_TOKEN'),
        'cep_origem' => env('MELHORENVIO_CEP_ORIGEM'),
        'url' => env('MELHORENVIO_URL', 'https://www.melhorenvio.com.br/api/v2/me/shipment/calculate'),
    ],
];
