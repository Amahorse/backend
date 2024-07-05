<?php

return [
    "app" => [
        "name" => "App",
        "version" => "3.3.9",
        "cache" => false,
        "development_mode" => true,
        "languages" => [
            "it",
            "en"
        ],
        "client_id" => "lJ4YyXzghqOoQgO8.e7233abf1f42adec51ed8df65106f635.16793916723YmK",
        "client_secret" => "4IX7lmrvcw7zfMjJ.252e53381d45393e6e332035d6c1fb79.1679391672ZqAy",
        "indexable" => false,
        "administrator" => "info@App.com",
        "skin" => "App",
        "protocol" => "http",
        "copyright" => "All Rights Reserved"
    ],
    "default" => [
        "id_countries" => 380,
        "language" => "it",
        "locale" => "it-IT"
    ],
    "db" => [
        "server" => "",
        "user" => "",
        "pass" => "",
        "charset" => "",
        "name" => ""
    ],
    "address" => [
        "cdn" => "http://cdn.app.localhost/"
    ],
    "domains" => [
        "api.amahorse.localhost" => [
            "app" => [
                "indexable" => false
            ],
            "token" => [
                "scopes" => ["administrator", "superadministrator", "reseller", "agent", "franchise", "user", "provisional", "not_confirmed"]
            ]
        ]
    ]
];

?>