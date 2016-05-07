<?php

namespace App\Providers;

use Fol;
use Fol\ServiceProviderInterface;
use Relay\RelayBuilder;
use Psr7Middlewares\Middleware as M;

class Middleware implements ServiceProviderInterface
{
    public function register(Fol $app)
    {
        $app['middleware'] = function ($app) {
            return (new RelayBuilder())->newInstance([
                M::basePath($app->getUrlPath()),
                M::ClientIp(),
                M::trailingSlash(),
                M::FormatNegotiator(),
                M::AuraRouter($app->get('router'))->arguments($app),
            ]);
        };
    }
}
