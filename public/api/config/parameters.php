<?php
    return [
         "token" => [
            "expire_time" => "24 hours",
            "identifier" => "email",
            "scopes" => ["user","administrator","superadministrator"],
            "algorithm" => "HS256"
        ]
    ]
?>