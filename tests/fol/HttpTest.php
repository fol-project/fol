<?php
use Fol\Http\Request;
use Fol\Http\Response;

class HttpTest extends PHPUnit_Framework_TestCase {
	public function testRequest () {
		$request = Request::create('/index');

		$this->assertEquals($request->getPath(), '/index');
		$this->assertEquals($request->getMethod(), 'GET');
		$this->assertEquals($request->getFormat(), 'html');
		$this->assertEquals($request->getLanguage(['en']), 'en');
		$this->assertFalse($request->isAjax());
		$this->assertEquals($request->getScheme(), parse_url(BASE_HOST, PHP_URL_SCHEME));
		$this->assertEquals($request->getHost(), parse_url(BASE_HOST, PHP_URL_HOST));

		//Change paths and formats
		$request->setPath('index2.XML');
		$this->assertEquals($request->getPath(), '/index2');
		$this->assertEquals($request->getFormat(), 'xml');
		
		$request->setFormat('JSON');
		$this->assertEquals($request->getFormat(), 'json');

		//Get full url
		$this->assertEquals($request->getUrl(), BASE_HOST.BASE_URL.'/index2.json');

		//GET params
		$request->get->set([
			'param1' => 1,
			'param2' => 2
		]);
		$this->assertEquals($request->get->get('param1'), 1);
		$this->assertEquals($request->get->get('param2'), 2);
		$this->assertEquals($request->get->get(), [
			'param1' => 1,
			'param2' => 2
		]);

		//Get full url with get params
		$this->assertEquals($request->getUrl(true, true, true), BASE_HOST.BASE_URL.'/index2.json?param1=1&param2=2');

		//Headers
		$request->headers->set('X-Requested-With', 'xmlhttprequest');
		$this->assertTrue($request->isAjax());

		//Language
		$language = $request->getLanguage(['gl-es', 'en']);
		$this->assertEquals($language, 'gl-es', $request->headers->get('Accept-Language'));

		$request->headers->set('Accept-Language', 'en,gl-es,es;q=0.5');

		$language = $request->getLanguage(['gl-es', 'en']);
		$this->assertEquals($language, 'en');

		return $request;
	}

	/**
	 * @depends testRequest
	 */
	public function testResponse (Request $request) {
		$response = new Response();
		$response->prepare($request);

		$this->assertEquals($response->getStatus(), 200);
		$this->assertEquals($response->getStatus(true), 'OK');
		$this->assertEquals($response->headers->get('Content-Type'), 'text/json; charset=UTF-8');
		$this->assertEquals($response->getContent(), '');

		//Modify some response properties
		$response->setStatus(202);
		$response->setContent('Hello world');

		$this->assertEquals($response->getStatus(), 202);
		$this->assertEquals($response->getStatus(true), 'Accepted');
		$this->assertEquals($response->getContent(), 'Hello world');

		//Redirection
		$response->redirect('http://site.com');

		$this->assertEquals($response->getStatus(), 302);
		$this->assertEquals($response->headers->get('location'), 'http://site.com');
	}

	public function testRequestCli () {
		$args = [
			'index.php',
			'POST', '/item/edit/25',
			'--title', 'New title',
			'--text', 'New text'
		];

		$request = Request::createFromCli($args);

		$this->assertEquals($request->getPath(), '/item/edit/25');
		$this->assertEquals($request->getMethod(), 'POST');
		$this->assertEquals($request->post->get('title'), 'New title');
		$this->assertEquals($request->post->get('text'), 'New text');
		$this->assertEquals($request->getHost(), parse_url(BASE_HOST, PHP_URL_HOST));
		$this->assertEquals($request->getUrl(), BASE_HOST.BASE_URL.'/item/edit/25.html');
	}
}
