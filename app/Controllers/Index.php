<?php
namespace App\Controllers;

class Index
{
    public function index($request, $response, $app)
    {
        $href = $app->get('router')->getUrl('phpinfo');

        echo '<h1>Ola mundo!!</h1><p><a href="'.$href.'">Ver o phpinfo</a></p>';
    }

    public function phpinfo()
    {
        phpinfo();
    }

    public function error($request, $response)
    {
        $response->getBody()->write($request->attributes->get('ERROR')->getMessage());
    }
}
