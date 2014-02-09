<?php
use Fol\Http\Request;

class BasicTest extends PHPUnit_Framework_TestCase {
	protected static $app;

	//Init your app before start the test case
	public static function setUpBeforeClass () {
		//self::$app = new App\App;
	}

	//Remove the app on finish the test case
	public static function tearDownAfterClass () {
		self::$app = null;
	}

	//Write your tests
	public function testApp () {
		/*
		$app = self::$app;
		$request = Request::create('/');
		$response = $app($request);

		$this->assertEquals($response->getStatus(), 200);
		$this->assertEquals($response->getContentType(), 'text/html');
		*/
	}
}
