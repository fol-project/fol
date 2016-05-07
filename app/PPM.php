<?php

namespace App;

putenv('APP_URL=http://127.0.0.1:8080/_server/app');

require_once dirname(__DIR__).'/bootstrap.php';

use App\App;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class PPM
{
    private $app;

    /**
     * Initialize the app
     */
    public function __construct()
    {
        $this->app = new App();
    }

    /**
     * Serve http requests
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * 
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->app->__invoke($request, $response);
    }
}
