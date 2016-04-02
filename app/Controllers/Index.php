<?php

namespace App\Controllers;

use App\App;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest as Request;

class Index
{
    public function index(Request $request, Response $response, App $app)
    {
        return $app['templates']->render('pages/index', [
            'content' => 'Ola mundo'
        ]);
    }
}
