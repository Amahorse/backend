<?php

return [
    "app" => [
        "name" => "App",
        "version" => "3.3.9",
        "cache" => false,
        "development_mode" => true,
        "cdn" => "http://cdn.app.localhost/",
        "languages" => [
            "it",
            "en"
        ],
        "client_id" => "xETZNsNHBMiDqLTV.45c19b29012947fa5c8f44c755fe901e.1698312544Z6SY",
        "client_secret" => "CAY54VNXLFqpccvk.9ba601a09a9834e770f201b3892f2508.1698312544f9V2",
        "indexable" => false,
        "administrator" => "info@App.com",
        "protocol" => "http",
        "copyright" => "All Rights Reserved"
    ],
    "default" => [
        "id_countries" => 380,
        "language" => "it",
        "locale" => "IT"
    ],
    "store" => [
        "id_stores" => 3,
    ],
    "db" => [
        "server" => "bottleuproduction.c6sthmahhial.eu-west-1.rds.amazonaws.com",
        "user" => "bottleupusern4me",
        "pass" => "P3ssW.0RdM4.4r.14APr3tre3!",
        "charset" => "utf8mb4",
        "name" => "bottleup_clear"
    ],
    "domains" => [
        "api.amahorse.localhost" => [
            "token" => [
                "scopes" => ["administrator", "superadministrator", "reseller", "agent", "franchise", "user", "provisional", "not_confirmed"]
            ]
        ]
    ]
];

?>