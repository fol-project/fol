<?php
namespace App\Controllers;

class Index
{
    public function index($request, $response, $app)
    {
        $href = $app->router->getUrl('phpinfo');

        echo '<h1>Ola mundo!!</h1><p><a href="'.$href.'">Ver o phpinfo</a></p>';
    }

    public function phpinfo($request)
    {
        phpinfo();
    }

    public function error($request, $response)
    {
        $response->write($exception->getMessage());
    }
}
