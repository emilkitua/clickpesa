<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ClickPesa Credentials
    |--------------------------------------------------------------------------
    |
    | These values are used to authenticate with the ClickPesa API.
    | You can set them in your .env file.
    |
    */

    'client_id' => env('CLICKPESA_CLIENT_ID'),

    'client_secret' => env('CLICKPESA_CLIENT_SECRET'),

    'base_url' => env('CLICKPESA_BASE_URL', 'https://api.clickpesa.com/third-parties'),

];
