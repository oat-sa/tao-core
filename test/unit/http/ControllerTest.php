<?php

namespace oat\tao\test\unit\http;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use oat\generis\test\TestCase;
use oat\tao\model\http\HttpController;
use oat\tao\model\http\HttpFlowTrait;
use oat\tao\model\http\HttpRequestHelperTrait;

class ControllerTest extends TestCase
{
    public function testControllerExtendsHttpTrait()
    {
        $this->assertArraySubset(
            [
                HttpRequestHelperTrait::class,
                HttpFlowTrait::class
            ],
            array_keys(class_uses(HttpController::class))
        );
    }

    public function testSetRequest()
    {
        $request = new ServerRequest('GET', '/');
        $controller = new ProxyHttpController();

        $this->assertInstanceOf(HttpController::class, $controller->setRequest($request));

        $property = new \ReflectionProperty(HttpController::class, 'request');
        $property->setAccessible(true);
        $controllerRequest = $property->getValue($controller);

        $this->assertSame($request, $controllerRequest);
    }

    public function testSetResponse()
    {
        $response = new Response();
        $controller = new ProxyHttpController();

        $this->assertInstanceOf(HttpController::class, $controller->setResponse($response));

        $property = new \ReflectionProperty(HttpController::class, 'response');
        $property->setAccessible(true);
        $controllerRequest = $property->getValue($controller);

        $this->assertSame($response, $controllerRequest);
    }
}

