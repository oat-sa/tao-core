<?php

namespace oat\tao\test\unit\oauth\lockout;

use PHPUnit\Framework\TestCase;
use oat\oatbox\log\LoggerService;
use oat\tao\model\oauth\lockout\IPFactory;
use oat\tao\model\oauth\lockout\IPLockout;
use oat\tao\model\oauth\lockout\storage\LockoutStorageInterface;
use oat\tao\model\oauth\lockout\storage\RdsLockoutStorage;
use PHPUnit\Framework\MockObject\MockObject;

class IPFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['HTTP_CLIENT_IP'] = '127.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.2';
    }

    public function testCreateDefault()
    {
        $factory = new IPFactory();
        $this->assertSame('127.0.0.1', $factory->create());
    }

    public function testCreateFromConfig()
    {
        $factory = new IPFactory(['HTTP_X_FORWARDED_FOR']);
        $this->assertSame('127.0.0.2', $factory->create());
    }
}
