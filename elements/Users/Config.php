<?php 

//TODO: rivedere se serve auth
return
[
    "users" => [
        "b2b_auto_confirm" => true,
        "b2b_vies_verify" => false
    ],
    "password" => [ 
        "validate" => "all", //Vedere Helpers Password per opzioni
        "minimum_length" => 6,
        "maximum_length" => 30
    ],
    "auth" => [
        0 => 'inactive',
        1000 => 'not_confirmed',
        2000 => 'banned',
        2500 => 'provisional',
        3000 => 'user',
        3400 => 'agent',
        3500 => 'reseller',
        3600 => 'accountant',
        3700 => 'logistic',
        3750 => 'printing',
        3800 => 'production',
        3900 => 'franchise',
        4000 => 'administrator',
        5000 => 'superadministrator'
    ]
    
];
?>