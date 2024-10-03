<?php

declare(strict_types=1);

namespace Kodelines;

use Kodelines\Tools\Folder;

class Config
{


    /**
     * Dominio corrente
     * 
     * @var string|bool 
     */
    public $domain = false;

    /**
     * Valori caricati nell'istanza 
     * 
     * @var array
     */
    public $values = [];



    /**
     * Contiene valori custom di base 
     * 
     * @var bool 
     */
    private $custom = [];

    /**
     * Configurazioni riservate che non vengono ritornate tramite Api
     *
     * @var array
     */

    public $reserved = [
        "app" => ["administrator","cache","cache_expire_time","development_mode"],
        "token",
        "db",
        "domains",
        "logs",
        "auth",
        "token",
        "facebook",
        "tracking",
        "dir",
        "stripe" => ["secret", "webhook_endpoint_secret"],
        "paypal" => ["client_secret"],
        "recaptcha" => ["secret"],
        "upload"
    ];


    /**
     * Il costruttore richiede una app ed eventualmente un dominio
     *
     * @method __construct
     * @param  string     $context  Application folder
     * @param  array      $options Contiene opzioni da passare direttamente al costruttore
     * @return object     Return this object
     */
    public function __construct($domain = false)
    {

        $this->domain = $domain;

        //Prendo i valori custom dalla app
        return $this->load();
    }


    /**
     * Configurazioni di default, NB: non dichiarare mai nomi con -, sempre con _ perchè vengono dichiarate costanti in automatico in load e darebbe errore php
     * 
     * @return array 
     */
    public function default(): array
    {

        $default = [
            "app" => [
                "name" => "Kodelines",
                "version" => System::VERSION,
                "cache" => true,
                "cache_expire_time" => 86400, //1 Day
                "development_mode" => true,
                "administrator" => "webmail@kodelines.com",
                "cdn" => "/",
                "languages" => ["it","en"] //Può essere array multiplo, se non trovata lingua attiva nel browser prende quella del gruppo default, ci va sempre messa per le lingue gestite da backend sulle tab contenuti, poi possono essere specificate quelle da attivare sul dominio specifico  
            ],
            "default" => [
                "language" => "it",
                "locale" => "it"
            ],
            "db" => [ //Necessari al funzionamento del db
                "server" => null,
                "user" => null,
                "pass" => null,
                "charset" => null,
                "name" => null
            ],
            "locales" => [
                "IT" =>[
                    "lcid" => "it-IT",
                    "id_countries" => 380,
                    "currency" => "EUR",
                    "currency_symbol" => "€",
                    "time_format" => "H:i",
                    "date_format" => "d-m-Y",
                    "timezone" => "Europe/Rome"
                ],
                "CH" => [
                    "lcid" => "de-CH",    
                    "currency" => "CHF",
                    "currency_symbol" => "CHF",
                    "time_format" => "H:i",
                    "date_format" => "d-m-Y",
                    "timezone" => "Europe/Rome"
                ],
                "US" => [
                    "lcid" => "en-US",   
                    "currency" => "USD",
                    "currency_symbol" => "$",
                    "time_format" => "H:i",
                    "date_format" => "Y-m-d",
                    "timezone" => "America/Los_Angeles"
                ],
            ],
            "dir" => [
                "logs" => _DIR_LOGS_,
                "uploads" => _DIR_UPLOADS_ //la directory uploads è definita su system start ma viene chiamata con la funzione _DIR_UPLOADS_ perchè può essere customizzata da questo parametro
            ],
            "upload" => [
                "allowed" => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'csv', 'xls', 'xlsx', 'doc', 'docx', 'pdf', 'txt'],
                "max_size" => ini_get('upload_max_filesize'),
                "images" => [
                    "minX" => 100,
                    "minY" => 100,
                    "maxX" => 5000,
                    "maxY" => 5000,
                    "generate_webp" => true, //Se true genera versione webp di tutti i file uploadati o con thumbnains
                    "generate_thumbnails" => true, //Se true genera automaticamente thumbnails immagini quando uploadati                
                    "random_names" => true, //Se a true i file uploadati avranno nome random
                    "resize_quality" => 80,  //Qualità del resize delle immagini in generazione thumbnails dopo upload
                    //Dimensioni standard thumbnails dopo upload
                    "thumbnails" => [
                        "micro" => ['x' => 10, 'y' => 10],
                        "thumb" => ['x' => 200, 'y' => 200],
                        "medium" => ['x' => 300, 'y' => 300],
                        "large" => ['x' => 800, 'y' => 800]
                    ]
                ],
                "files" => [
                    "random_names" => true
                ]
            ],
            "domains" => [] //Può contenere vari domini per applicazione, dentro ci vanno anche i cron runner su array ["cron" => ["tempo" => "comando"]]
        ];


        foreach(Folder::read(_DIR_ELEMENTS_) as $element) {

            if($config = self::getFile($element)) {
                $default = array_replace_recursive($default,$config);
            }

        }


        return $default;
        
    }

    /**
     * Invoking the class return get the param
     *
     * @param string $group
     * @param boolean $value
     * @return void
     */
    public function __invoke(string $group, $value = false)
    {
        return $this->get($group, $value);
    }

    /**
     * Genera configurazioni in base a custom e domini caricati
     *
     * @return object
     */
    public function generate(): object
    {
        
        //Se custom non è caricato o con errore skippa tutto
        if (!empty($this->custom) && is_array($this->custom)) {


            $this->values = $this->custom;

            //Parso configurazioni dominio corrente se settate che hanno la precedenza su tutto il resto e sovrascrivono  variabili in array principale
            //Altrimenti viene restituito array con configuraizoni domini cosi come è per fare controlli
            if ($this->domain && isset($this->custom['domains']) && isset($this->custom['domains'][$this->domain])) {

                //Sovrascrivo configurazioni per dominio
                $this->values = array_replace_recursive($this->values, $this->custom['domains'][$this->domain]);
            }
        }
      
        //Parso con lo stesso loop configurazioni di default per non avere variabili indefinite 
        $this->values = array_replace_recursive($this->default(), $this->values);
 

        return $this;
    }


    /**
     *  Prende il file di configurazione di una app
     *
     * @method get
     * @param  string         $app  Application folder
     * @return array         multiple array with all config types
     */
    public function load(): object
    {

        $file = _DIR_CONFIG_ . 'config.php';

        //Ritorna configurazioni di default se non esiste file configurazione
        if (!file_exists($file) || !$this->custom = require($file)) {
            throw new Error($file . ' file is not well formatted');
        }
     
        return $this->generate();
    }




    /**
     * Get a config group or a single group from loaded config vars
     *
     * @method get
     * @param  string  $group   Config group name (key of json), in case of .php config is the name of the php file (no extension)
     * @param  boolean $value   Optional config group value
     * @param  boolean $check   Se è true è come se sovrascrivesse temporarnamente valore strict per controllo esisteza campo
     * @return mixed            False if not found, array if config group or value if config value
     */
    public function get(string $group, $value = false): mixed
    {

        if (!isset($this->values[$group])) {
            return false;
        }

        if ($value) {
            
            if (!array_key_exists($value,$this->values[$group])) {

                return false;
            }

            return $this->values[$group][$value];
        }

        return $this->values[$group];
    }

    /**
     * Setta un valore di configurazione in un gruppo specifico, il contenuto non può essere sovrascritto
     * TODO: capire se serve più o meno questa funzione
     *
     * @param string $group
     * @param string $value
     * @param mixed $content
     * @return void
     */
    public function set(string $group, string $value, mixed $content)
    {

        if (!isset($this->values[$group])) {
            throw new Error("Cannot set '" . $value . "' in '" . $group . "' in config, group does not exists ");
        }

        $this->values[$group][$value] = $content;
    }


    /**
     * Fa il setup di un nuovo dominio e rigenera le configurazioni
     *
     * @param  string $domain
     * @return object
     */
    public function setDomain(string $domain): object
    {

        //Se in modalità strict e non è stato configurato il dominio da errore di sistema
        if (!isset($this->custom['domains'][$domain])) { 
            throw new Error('Domain ' . $domain . ' non configured on ' . _DIR_CONFIG_ . 'app.json');
        }

        $this->domain = $domain;

        return $this->generate();
    }


    /**
     * Definisce costanti in base alle configurazioni correnti, da usare per template engine per non farle sovrascrivere
     *
     * @return object
     */
    public function define(): object
    {

        //Configurazioni normali
        foreach ($this->values as $group => $param) {

            if (!is_array($param)) {

                $varname = mb_strtoupper('_' . $group . '_');

                if (!defined($varname)) {
                    define($varname, $param);
                }

                continue;
            }


            foreach ($param as $params => $value) {

                if (is_array($value)) {
                    continue;
                }

                $varname = mb_strtoupper('_' . $group . '_' . $params . '_');

                if (!defined($varname)) {
                    define($varname, $value);
                }
            }
        }



        return $this;
    }


    /**
     *  Prende il file di configurazione di una app
     *
     * @method get
     * @param  string         $app  Application folder
     * @return array         multiple array with all config types
     */
    public function getFile(string $element): array|bool
    {

        if(file_exists(_DIR_ELEMENTS_ . $element . '/Config.php')) {

            $config = require(_DIR_ELEMENTS_ . $element . '/Config.php');

            if(is_array($config)) {
                return $config;
            }

        }

        return false;

    }

}
