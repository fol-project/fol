<?php

namespace App\Tests;

class BasicTest extends Base
{
    public function testApp()
    {
        $response = static::get('/');

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAdmin()
    {
        $response = static::get('/admin');

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
