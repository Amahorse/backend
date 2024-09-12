<?php 
return
[
    "store" =>
    [
        "type" => 'b2c',
        "id_resellers" => null,
        "id_stores" => 3,
        "id_agents" => null,
        "id_countries" => null,
        "payment_commission_percentage" => 0.034,
        "payment_method" => "stripe,paypal",
        "price_display_taxes_excluded" => 0,
        "checkout_mode" => "site",
        "enable_b2c" => true,
        "enable_b2b" => true,
        "enable_horeca" => true,
        "shipping_delay" => 1, //Ritrado spedizione in giorni lavorativi
        "shipping_max_hour" => "12:00", //Orario entro cui va ordinato per mettere l'ordine in lavorazione da subito senza shipping_delay
        "minimum_order_quantity" => null,
        "maximum_order_quantity" => null,
        "minimum_order_price" => null,
        "locale" => null,
        "holidays" => ['01-01', '01-06', '04-25', '05-01', '06-02', '08-15', '11-01', '12-08', '12-25', '12-26'] //Giorni festivi per calcolo spedizioni
    ]
    
];
?>