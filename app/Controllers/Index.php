<?php
namespace App\Controllers;

use Fol\Http\Response;

class Index
{
    public function index($request)
    {
        $templates = $this->app->templates;

        $templates->saveRender('content', '<h1>Ola mundo!!</h1><p><a href="phpinfo">Ver o phpinfo</a></p>');

        return $templates->render('html.php');
    }

    public function phpinfo($request)
    {
        phpinfo();
    }

    public function error($request, $response)
    {
        $exception = $request->parameters->get('exception');

        $response->setContent($exception->getMessage());
    }
}
