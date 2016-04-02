<?php

namespace App\Providers;

use Fol;
use Fol\ServiceProviderInterface;
use League\Plates\Engine;

class Templates implements ServiceProviderInterface
{
    public function register(Fol $app)
    {
        $app['templates'] = function ($app) {
            return new Engine($app->getPath('templates'));
        };
    }
}
