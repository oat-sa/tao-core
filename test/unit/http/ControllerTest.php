<?php

namespace oat\tao\test\unit\http;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use oat\tao\model\http\Controller;
use oat\tao\model\http\HttpFlowTrait;
use oat\tao\model\http\HttpRequestHelperTrait;

class ControllerTest extends TestCase
{
    public function testControllerExtendsHttpTrait()
    {
        $this->assertSame(
            [
                HttpRequestHelperTrait::class,
                HttpFlowTrait::class
            ],
            array_keys(class_uses(Controller::class))
        );
    }

    public function testSetRequest()
    {
        $request = new ServerRequest('GET', '/');
        $controller = new ProxyController();

        $this->assertInstanceOf(Controller::class, $controller->setRequest($request));

        $property = new \ReflectionProperty(Controller::class, 'request');
        $property->setAccessible(true);
        $controllerRequest = $property->getValue($controller);

        $this->assertSame($request, $controllerRequest);
    }

    public function testSetResponse()
    {
        $response = new Response();
        $controller = new ProxyController();

        $this->assertInstanceOf(Controller::class, $controller->setResponse($response));

        $property = new \ReflectionProperty(Controller::class, 'response');
        $property->setAccessible(true);
        $controllerRequest = $property->getValue($controller);

        $this->assertSame($response, $controllerRequest);
    }
}
