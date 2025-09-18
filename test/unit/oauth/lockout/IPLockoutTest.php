<?php

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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\oauth\lockout;

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\log\LoggerService;
use oat\tao\model\oauth\lockout\IPFactory;
use oat\tao\model\oauth\lockout\IPLockout;
use oat\tao\model\oauth\lockout\storage\LockoutStorageInterface;
use oat\tao\model\oauth\lockout\storage\RdsLockoutStorage;
use PHPUnit\Framework\MockObject\MockObject;

class IPLockoutTest extends TestCase
{
    use ServiceManagerMockTrait;

    private LockoutStorageInterface|MockObject $lockStorageMock;
    private IPLockout $lockoutService;

    protected function setUp(): void
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
            $this->getServiceManagerMock([LoggerService::SERVICE_ID => $loggerMock])
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
