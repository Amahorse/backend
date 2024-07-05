<?php

declare(strict_types=1);


namespace Context\Cli;

class Register
{

    /**
     * Registro comandi console
     *
     * @var array
     */
    protected static $registry = [];

    /**
     * Registra comando console
     *
     * @param string $name
     * @param string|callable $callable
     * @return void
     */
    public static function register(string $name, string|callable $callable)
    {
        self::$registry[$name] = $callable;
    }

    /**
     * Recupera comando console
     *
     * @param string $command
     * @return string|null
     */
    public static function get(string $command): string|bool
    {
        return isset(self::$registry[$command]) ? self::$registry[$command] : false;
    }

}
