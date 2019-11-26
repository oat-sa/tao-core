<?php

declare(strict_types=1);

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\http;

use GuzzleHttp\Psr7\ServerRequest;
use oat\generis\test\TestCase;
use oat\tao\model\http\Controller;

class HttpRequestHelperTraitTest extends TestCase
{
    /**
     * @dataProvider httpMethodProvider()
     * @param string $method Http Method to be tested
     */
    public function testHttpHeaders($method): void
    {
        $headers = ['toto' => 'header', 'titi' => 'headers'];
        $request = new ServerRequest($method, '/', $headers);

        $controller = new ProxyController();
        $this->assertInstanceOf(Controller::class, $controller->setRequest($request));

        $this->assertSame($headers, $controller->getHeaders());

        $this->assertTrue($controller->hasHeader('titi'));
        $this->assertFalse($controller->hasHeader('notexist'));

        $this->assertSame(['headers'], $controller->getHeader('titi'));
        $this->assertEmpty($controller->getHeader('notexist'));
    }

    /**
     * @dataProvider httpMethodProvider()
     * @param string $method Http Method to be tested
     */
    public function testHttpPostParameters($method): void
    {
        $parameters = ['toto' => 'header', 'titi' => 'headers'];
        $request = new ServerRequest($method, '/', $parameters);
        $request = $request->withParsedBody($parameters);

        $controller = new ProxyController();
        $this->assertInstanceOf(Controller::class, $controller->setRequest($request));

        $this->assertSame($parameters, $controller->getPostParameters());

        $this->assertTrue($controller->hasPostParameter('titi'));
        $this->assertFalse($controller->hasPostParameter('notexist'));

        $this->assertSame('headers', $controller->getPostParameter('titi'));
        $this->assertEmpty($controller->getPostParameter('notexist'));
    }

    /**
     * @dataProvider httpMethodProvider()
     * @param string $method Http Method to be tested
     */
    public function testHttpGetParameters($method): void
    {
        $parameters = ['toto' => 'header', 'titi' => 'headers'];
        $request = new ServerRequest($method, '/', $parameters);
        $request = $request->withQueryParams($parameters);

        $controller = new ProxyController();
        $this->assertInstanceOf(Controller::class, $controller->setRequest($request));

        $this->assertSame($parameters, $controller->getGetParameters());

        $this->assertTrue($controller->hasGetParameter('titi'));
        $this->assertFalse($controller->hasGetParameter('notexist'));

        $this->assertSame('headers', $controller->getGetParameter('titi'));
        $this->assertEmpty($controller->getGetParameter('notexist'));
    }

    /**
     * @dataProvider httpMethodProvider()
     * @param string $method Http Method to be tested
     */
    public function testHttpAttributesParameter($method): void
    {
        $parameters = ['toto' => 'header', 'titi' => 'headers'];
        $request = new ServerRequest($method, '/', $parameters);
        foreach ($parameters as $attribute => $value) {
            $request = $request->withAttribute($attribute, $value);
        }

        $controller = new ProxyController();
        $this->assertInstanceOf(Controller::class, $controller->setRequest($request));

        $this->assertSame($parameters, $controller->getAttributeParameters());

        $this->assertTrue($controller->hasAttributeParameter('titi'));
        $this->assertFalse($controller->hasAttributeParameter('notexist'));

        $this->assertSame('headers', $controller->getAttributeParameter('titi'));
        $this->assertEmpty($controller->getAttributeParameter('notexist'));
    }

    /**
     * @dataProvider httpMethodProvider()
     * @param string $method Http Method to be tested
     */
    public function testHttpCookie($method): void
    {
        $parameters = ['toto' => 'header', 'titi' => 'headers'];
        $request = new ServerRequest($method, '/', $parameters);
        $request = $request->withCookieParams($parameters);

        $controller = new ProxyController();
        $this->assertInstanceOf(Controller::class, $controller->setRequest($request));

        $this->assertSame($parameters, $controller->getCookieParams());

        $this->assertTrue($controller->hasCookie('titi'));
        $this->assertFalse($controller->hasCookie('notexist'));

        $this->assertSame('headers', $controller->getCookie('titi'));
        $this->assertEmpty($controller->getCookie('notexist'));
    }

    /**
     * @dataProvider httpMethodProvider()
     * @param string $method Http Method to be tested
     */
    public function testHttpMethod($method): void
    {
        $request = new ServerRequest($method, '/');
        $controller = new ProxyController();
        $this->assertInstanceOf(Controller::class, $controller->setRequest($request));

        $this->assertSame($method, $controller->getRequestMethod());

        $name = 'isRequest' . $method;
        $this->assertTrue($controller->{$name}());
    }

    public function httpMethodProvider()
    {
        return [
            ['GET'],
            ['POST'],
            ['PUT'],
            ['HEAD'],
            ['DELETE'],
        ];
    }

    public function testHttpHelpers(): void
    {
        $request = new ServerRequest(
            'GET',
            '/uri/path?query=string',
            [
                'content-type' => 'titi',
                'user-agent' => 'toto',
            ],
            null,
            '1.1',
            [
                'HTTP_X_REQUESTED_WITH' => 'xmlhttprequest',
            ]
        );

        $controller = new ProxyController();
        $this->assertInstanceOf(Controller::class, $controller->setRequest($request));

        $this->assertTrue($controller->isXmlHttpRequest());
        $this->assertSame(['toto'], $controller->getUserAgent());
        $this->assertSame(['titi'], $controller->getContentType());
        $this->assertSame('/uri/path', $controller->getRequestURI());
        $this->assertSame('query=string', $controller->getQueryString());
    }

    public function testEmptyHttpHelpers(): void
    {
        $request = new ServerRequest('GET', '/');

        $controller = new ProxyController();
        $this->assertInstanceOf(Controller::class, $controller->setRequest($request));

        $this->assertFalse($controller->isXmlHttpRequest());
        $this->assertEmpty($controller->getUserAgent());
        $this->assertEmpty($controller->getContentType());
        $this->assertSame('/', $controller->getRequestURI());
        $this->assertSame('', $controller->getQueryString());
    }
}
