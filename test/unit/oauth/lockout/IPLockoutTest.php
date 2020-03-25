<?php

namespace oat\tao\test\unit\oauth\lockout;

use oat\generis\test\TestCase;
use oat\oatbox\log\LoggerService;
use oat\tao\model\oauth\lockout\IPFactory;
use oat\tao\model\oauth\lockout\IPLockout;
use oat\tao\model\oauth\lockout\storage\LockoutStorageInterface;
use oat\tao\model\oauth\lockout\storage\RdsLockoutStorage;
use oat\generis\test\MockObject;

class IPLockoutTest extends TestCase
{
    /**
     * @var LockoutStorageInterface|MockObject
     */
    private $lockStorageMock;
    /**
     * @var IPLockout
     */
    private $lockoutService;

    public function setUp(): void
    {
        $this->lockStorageMock = $this->createMock(RdsLockoutStorage::class);

        $ipFactoryMock = $this->createMock(IPFactory::class);
        $ipFactoryMock->method('create')->willReturn('127.0.0.1');

        $loggerMock = $this->createMock(LoggerService::class);

        $this->lockoutService = new IPLockout([
            IPLockout::OPTION_THRESHOLD       => 2,
            IPLockout::OPTION_TIMEOUT         => 10,
            IPLockout::OPTION_IP_FACTORY      => $ipFactoryMock,
            IPLockout::OPTION_LOCKOUT_STORAGE => $this->lockStorageMock
        ]);
        $this->lockoutService->setServiceLocator(
            $this->getServiceLocatorMock([LoggerService::SERVICE_ID => $loggerMock])
        );
    }

    public function testIsAllowed()
    {
        $this->lockStorageMock->method('getFailedAttempts')->willReturn(1);
        $this->assertTrue($this->lockoutService->isAllowed());
    }

    public function testBlocked()
    {
        $this->lockStorageMock->method('getFailedAttempts')->willReturn(4);
        $this->assertFalse($this->lockoutService->isAllowed());
    }

    public function testLogFailedAttempt()
    {
        $this->lockStorageMock->expects($this->once())->method('store')->with('127.0.0.1', 10);
        $this->lockoutService->logFailedAttempt();
    }
}
