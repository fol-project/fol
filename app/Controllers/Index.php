<?php
namespace App\Controllers;

class Index
{
    public function index($request, $response, $app)
    {
        $templates = $app->templates;

        $href = $app->router->getUrl('phpinfo');

        $templates->saveRender('content', '<h1>Ola mundo!!</h1><p><a href="'.$href.'">Ver o phpinfo</a></p>');

        return $templates->render('html.php');
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
