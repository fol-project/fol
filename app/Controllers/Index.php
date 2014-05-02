<?php
namespace App\Controllers;

class Index
{
    public function index($request, $response, $app)
    {
        $href = $app->router->getUrl('phpinfo');

        $app->templates->saveRender('content', '<h1>Ola mundo!!</h1><p><a href="'.$href.'">Ver o phpinfo</a></p>');

        return $app->templates->render('html.php');
    }

    public function phpinfo($request)
    {
        phpinfo();
    }

    public function error($request, $response)
    {
        $exception = $request->route->get('exception');

        $response->setContent($exception->getMessage());
    }
}
