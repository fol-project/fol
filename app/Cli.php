<?php
namespace App;

use Fol\Http\Request;
use Fol\Terminal;

class Cli extends Terminal
{
    //Run a request
    public static function run($method = 'GET', $url = '/')
    {
        $request = Request::create($url, $method, func_get_arg(2));

        $app = new App();
        $app($request)->send();
    }

    //Place here your custom functions...
}
