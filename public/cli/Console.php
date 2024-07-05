<?php

declare(strict_types=1);


namespace Context\Cli;

use Kodelines\Log;

class Console
{

    /**
     * Contiene i parametri passati alla console
     *
     * @var array
     */
    public $data = [];

    /**
     * Contiene il comando passato alla console
     *
     * @var string
     */
    public string $command;

    /**
     * Il constructor fa il parse degli argomenti passati in console
     */
    public function __construct()
    {
        $this->parse($_SERVER['argv']);
    }

    /**
     * Registra un comando per la console nella classe statica register
     *
     * @param string $name
     * @param string|callable $callable
     * @return void
     */
    public function register(string $name, string|callable $callable)
    {
       Register::register($name,$callable);
    }

    /**
     * Inizializza la console poi va in exit, possono essere eseguiti sotto comandi all'interno di script
     *
     * @return void
     */
    public function start()
    {

        if (empty($this->command)) {
            $this->exit("Command is empty");
        }

        if(!$command = Register::get($this->command)) {
            $this->exit("Command \"$command\" not registered.");
        }

        $this->run($command,$this->data);

        exit;
    }

    /**
     * Fa la run di un comando, può essere chiamato anche all'interno delle interface Command per eseguire sotto comandi
     *
     * @param string $command
     * @param array $args
     * @return void
     */
    public function run(string $command, array $args = [])
    {

        if (is_callable($command)) {

            call_user_func($command, $args);

        } else {

            $split = preg_split("/\:/", $command);

            if (empty($split[0]) || empty($split[1])) {
                $this->exit("Command \"$command\" not valid.");
            }

            $classname = '\\' . $split[0];

            $method = $split[1];


            if (class_exists($classname.'::class',true)) {
                $this->exit("Class \"$classname\" not found.");
            }

            $class = new $classname($this);

            if(!method_exists($class,$method)) {
                $this->exit("Method \"$classname\"::\"$method\" not found.");
            }

            $class->$method($args);
        }

        //Write time on page debug when script is finished
        new Log("cli",round((microtime(true) - _TIMESTART_), 4), ["command" => $command]);

        $this->display('Operation Completed');

    }

    /**
     * Stampa una stringa
     *
     * @param string $message
     * @return void
     */
    public function out(string $message)
    {
        print($message);
    }

    /**
     * Genera nuova linea nella console
     *
     * @return void
     */
    public function newline()
    {
        $this->out("\r\n");
    }

    /**
     * Fa il display di un output su una nuova riga
     *
     * @param string $message
     * @return void
     */
    public function display(string $message)
    {
 
        $this->out($message);
        $this->newline();
    }

    /**
     * Genera errore console ma continua lo script
     *
     * @param [type] $message
     * @param boolean $exit
     * @return void
     */
    public function error(string $message)
    {

        //Write time on page debug when script is finished
        new Log("cli",round((microtime(true) - _TIMESTART_), 4), ["error" => $message]);

        $this->display("ERROR: " . $message);

    }

    /**
     * Genera errore console poi va in exit
     *
     * @param string $message
     * @param boolean $exit
     * @return void
     */
    public function exit(string $message)
    {

        $this->error($message);
  
        exit;
        
    }


    /**
     * Fa il parsing degli argomenti della console generando array con chiavi numeriche o coppie chiave valore 
     * Nella console se si passa solo argomento va con chiave numerica altrimenti con chiave:valore viene assegnato l'array
     *
     * @param array $argv
     * @return void
     */
    public function parse(array $argv)
    {

        if (empty($argv[2])) {
            $this->error('Command not set', true);
        }

        $this->command = $argv[2];

        foreach ($argv as $key => $val) {

            if ($key >= 3) {

                $split = preg_split("/\:/", $val);

                //Fa lo split del valore nella console va messo il parametro come chiave:valore
                if (empty($split[0]) || empty($split[1])) {
                    $this->data[] = $val;    
                } else {
                    $this->data[$split[0]] = $split[1];  
                }

                
            }
        }
    }
}
