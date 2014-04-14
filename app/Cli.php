<?php
namespace App;

use Fol\Terminal;
use Fol\Http\Request;

class Cli
{
    public static function execute(array $argv)
    {
        $fnName = $argv[1];

        //Execute a defined function
        if (method_exists('App\\Cli', $fnName)) {
            return self::$fnName($argv);
        }

        //or execute a request
        App::runCli();
    }


    /**
     * Edit configuration values from cli
     *
     * Example:
     * $ php fol config database
     */
    public static function config(array $options)
    {
        $app = new App();
        $name = isset($options[2]) ? $options[2] : null;

        if (!$name || !($config = $app->config->get($name))) {
            die("The config '$name' is not defined");
        }

        foreach ($config as $k => &$value) {
            $value = Terminal::prompt("Config > {$name}.{$k} = '{$value}' > ", $value);
        }

        $app->config->set($name, $config)->saveFile($name);
    }

    // Place here your custom functions...
}
