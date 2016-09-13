<?php

return [
    'grant_types' => [
    //     'password' => [
    //         'class' => 'League\OAuth2\Server\Grant\PasswordGrant',
    //         'access_token_ttl' => 604800,
    //         'callback' => '\App\PasswordGrantVerifier@verify',
    //     ],
        // 'refresh_token' => [
        //     'class' => '\League\OAuth2\Server\Grant\RefreshTokenGrant',
        //     'access_token_ttl' => 3600,
        //     'refresh_token_ttl' => 36000
        // ]
            'custom' => [
                'class' => 'Odminchek\OAuth2Server\Grant\CustomGrant',
                'access_token_ttl' => 604800,
                'callback' => '\App\PasswordGrantVerifier@verify',
            ],
    ],

    'token_type' => 'League\OAuth2\Server\TokenType\Bearer',
    'state_param' => false,
    'scope_param' => false,
    'scope_delimiter' => ',',
    'default_scope' => null,
    'access_token_ttl' => 3600,
    'limit_clients_to_grants' => false,
    'limit_clients_to_scopes' => false,
    'limit_scopes_to_grants' => false,
    'http_headers_only' => false,
];
