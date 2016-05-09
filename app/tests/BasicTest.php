<?php

use App\App;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

class BasicTest extends PHPUnit_Framework_TestCase
{
    protected static $app;

    public static function setUpBeforeClass()
    {
        static::$app = new App();
    }

    public function testApp()
    {
        $response = static::$app->dispatch(new ServerRequest(), new Response());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
