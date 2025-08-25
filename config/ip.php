<?php

return [
    'pagination' => [
        'default_per_page' => env('IP_DEFAULT_PER_PAGE', 15),
        'max_per_page' => env('IP_MAX_PER_PAGE', 100),
    ],
    
    'validation' => [
        'max_search_length' => env('IP_MAX_SEARCH_LENGTH', 255),
        'max_country_length' => env('IP_MAX_COUNTRY_LENGTH', 100),
        'max_city_length' => env('IP_MAX_CITY_LENGTH', 100),
    ],
];