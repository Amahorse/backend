<?php


    $famiglia = [
        'MWES' => 'western_riding', 
        'MING' => 'english_riding',
        'AMEF' => 'others_riding',
        'MIMW' => 'western_riding,english_riding',
        'PET' => 'pet',
        'SCDA' => 'stable'
    ];

    $sottofamiglia = [
        'CAVA' => ['type' => 'horse'],
        'CAVK' => ['type' => 'rider', 'age' => 'young', 'gender' => 'male,female'],	
        'CAVM' => ['type' => 'rider', 'age' => 'adult', 'gender' => 'male'],
        'CAVU' => ['type' => 'rider', 'age' => 'adult', 'gender' => 'male,female'],
        'CAVW' => ['type' => 'rider', 'age' => 'adult', 'gender' => 'female'],
        'KIDM' => ['type' => 'rider', 'age' => 'young', 'gender' => 'male'],
        'KIDW' => ['type' => 'rider', 'age' => 'young', 'gender' => 'female'],
    ];


    /*
        AMON	Altre Monte	Other Ridings
        CURA	Cura del Cavallo	Horse Care
        DOG	Cane	Dog
        ELET	Attrezzatura Elettrica	Electrical Equipment
        FINI	Finimenti	Harness
        SCUD	Attrezzature da Scuderia	Stable Equipment
    */
    
?>