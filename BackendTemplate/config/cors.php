<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Lista os dominios do frontend separados por virgula no .env, ex:
    // FRONTEND_ALLOWED_ORIGIN=http://localhost:5173,https://loja-cliente.com.br
    'allowed_origins' => array_filter(explode(',', env('FRONTEND_ALLOWED_ORIGIN', ''))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Precisa ser true pro Sanctum autenticar via cookie (statefulApi()).
    // So funciona junto com allowed_origins explicito -- nunca com '*'.
    'supports_credentials' => true,

];
