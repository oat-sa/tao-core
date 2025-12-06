<?php

namespace oat\tao\test\unit\auth;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use oat\tao\model\auth\BasicType;

/**
 * Class BasicAuthTypeTest
 * @package oat\tao\test\unit\auth
 */
class BasicTypeTest extends TestCase
{
    public function testBasicType()
    {
        $authType = new TestBasicAuthType();
        $credentials = [
            'login' => 'testLogin',
            'password' => 'testPassword'
        ];
        $authType->setCredentials($credentials);

        /** @var Request $requestMock */
        $requestMock = $this->createMock(Request::class);

        $clientMock = $this->createMock(Client::class);

        $clientMock
            ->expects($this->once())
            ->method('send')
            ->with($requestMock, ['auth' => ['testLogin', 'testPassword'], 'verify' => false]);

        $authType->setClient($clientMock);

        $authType->call($requestMock);
    }

    public function testFailedValidationBasicType()
    {
        $authType = new TestBasicAuthType();
        $credentials = [
            'loginFaild' => 'testLogin',
            'password' => 'testPassword'
        ];
        $authType->setCredentials($credentials);

        /** @var Request $requestMock */
        $requestMock = $this->createMock(Request::class);

        $clientMock = $this->createMock(Client::class);
        $this->expectException(\common_exception_ValidationFailed::class);

        $authType->setClient($clientMock);

        $authType->call($requestMock);
    }
}

class TestBasicAuthType extends BasicType
{
    private $client;

    public function setClient($clientMock)
    {
        $this->client = $clientMock;
    }

    protected function getClient($clientOptions = [])
    {
        return $this->client;
    }
}
