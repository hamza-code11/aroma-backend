<?php

return [
    'api_login_id' => env('AUTHORIZE_API_LOGIN_ID'),
    'transaction_key' => env('AUTHORIZE_TRANSACTION_KEY'),
    'environment' => env('AUTHORIZE_ENVIRONMENT', 'sandbox'),
];