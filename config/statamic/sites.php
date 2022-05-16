<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sites
    |--------------------------------------------------------------------------
    |
    | Each site should have root URL that is either relative or absolute. Sites
    | are typically used for localization (eg. English/French) but may also
    | be used for related content (eg. different franchise locations).
    |
    */

    'sites' => [

        'default' => [
            'name' => 'Slovensko',
            'locale' => 'sl',
            'url' => '/',
        ],
        'english' => [
            'name' => 'English',
            'locale' => 'en',
            'url' => '/en/',
        ],

    ],
];
