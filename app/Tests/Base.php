<?php

namespace App\Tests;

use App\App;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

abstract class Base extends \PHPUnit_Framework_TestCase
{
    protected static $app;

    public static function setUpBeforeClass()
    {
        static::$app = new App();
    }

    /**
     * Creates a server request.
     * 
     * @param string $uri
     * 
     * @return ServerRequest
     */
    protected static function request($uri = '/', $method = 'GET')
    {
        return new ServerRequest([], [], static::$app->getUrl($uri));
    }

    /**
     * Dispatch a request.
     * 
     * @param ServerRequest $request
     * 
     * @return Response
     */
    protected static function dispatch(ServerRequest $request)
    {
        return static::$app->dispatch($request, new Response());
    }

    /**
     * Creates and dispatch a GET request.
     * 
     * @param string $uri
     * @param array  $query
     * 
     * @return Response
     */
    protected static function get($uri, array $query = [])
    {
        return static::dispatch(static::request($uri, 'GET')->withQueryParams($query));
    }

    /**
     * Creates and dispatch a POST request.
     * 
     * @param string $uri
     * @param array  $data
     * 
     * @return Response
     */
    protected static function post($uri, array $data = [])
    {
        return static::dispatch(static::request($uri, 'GET')->withParsedBody($data));
    }
}
