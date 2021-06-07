<?php

return [
    'auto_includes' => [

        /*
         * If enabled relations who's names are present in the `include` request parameter
         * will be loaded automatically.
         */
        'enabled' => true,

        /*
         * The name of key in the request to where we should look for the includes to include.
         */
        'request_key' => 'include',
    ],
];
