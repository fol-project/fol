<?php
namespace App\Controllers;

class Index
{
    public function index($request, $response, $app)
    {
        $href = $app->router->getUrl('phpinfo');

        return $app->templates->render('html.php', [
            'content' => '<h1>Ola mundo!!</h1><p><a href="'.$href.'">Ver o phpinfo</a></p>'
        ]);
    }

    public function phpinfo($request)
    {
        phpinfo();
    }

    public function error($request, $response)
    {
        $exception = $request->route->get('exception');
        
        $response->write($exception->getMessage());
    }
}
