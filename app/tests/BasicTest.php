<?php
use App\App;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class BasicTest extends PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = new App();
    }

    public function testApp()
    {
        $response = $this->app->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
    }
}
