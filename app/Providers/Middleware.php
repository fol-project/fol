<?php

namespace App\Providers;

use Fol;
use Fol\ServiceProviderInterface;
use Relay\RelayBuilder;
use Psr7Middlewares\Middleware;

class Middleware implements ServiceProviderInterface
{
    public function register(Fol $app)
    {
        $app['middleware'] = function ($app) {
            return (new RelayBuilder())->newInstance([
                Middleware::basePath($app->getUrlPath()),
                Middleware::ClientIp(),
                Middleware::FormatNegotiator(),
                Middleware::AuraRouter($app->get('router'))->arguments($app),
            ]);
        };
    }
}
