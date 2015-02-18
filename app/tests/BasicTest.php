<?php
use Fol\Http\Request;

class BasicTest extends PHPUnit_Framework_TestCase
{
    protected static $app;

    //Init your app before start the test case
    public static function setUpBeforeClass()
    {
        self::$app = new App\App();
    }

    //Remove the app on finish the test case
    public static function tearDownAfterClass()
    {
        self::$app = null;
    }

    //Write your tests
    public function testApp()
    {
        $response = self::$app->runHttp(new Request('/'));

        $this->assertInstanceOf('Fol\\Http\\Response', $response);
    }
}
