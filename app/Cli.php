<?php
namespace app;

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
        self::executeRequest($argv);
    }

    /**
     * Execute a request from cli
     *
     * Example:
     * $ php fol GET /some/path?param=value
     */
    public static function executeRequest(array $options)
    {
        $options = Terminal::parseOptions($options, [
            1 => ['GET', 'POST', 'HEAD', 'PUT', 'DELETE'],
            2 => Terminal::OPTION_REQUIRED
        ]);

        $method = $options[0][1];
        $url = BASE_URL.$options[0][2];
        $params = $options[1];

        $request = Request::create($url, $method, $params);

        $app = new App();
        $app($request)->send();

        die();
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
