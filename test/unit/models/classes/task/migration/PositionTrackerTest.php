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
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\task\migration;

use common_persistence_KeyValuePersistence;
use oat\generis\persistence\PersistenceManager;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\task\migration\service\PositionTracker;

class PositionTrackerTest extends TestCase
{
    use ServiceManagerMockTrait;

    private PositionTracker $subject;
    private common_persistence_KeyValuePersistence|MockObject $persistenceMock;

    protected function setUp(): void
    {
        $this->subject = new PositionTracker();
        $persistenceManagerMock = $this->createMock(PersistenceManager::class);
        $this->persistenceMock = $this->createMock(common_persistence_KeyValuePersistence::class);

        $persistenceManagerMock
            ->method('getPersistenceById')
            ->willReturn($this->persistenceMock);

        $this->subject->setServiceLocator($this->getServiceManagerMock(
            [
                PersistenceManager::SERVICE_ID => $persistenceManagerMock,
            ]
        ));
    }

    public function testGetLastPositionWhenStartDefined(): void
    {
        $this->persistenceMock
            ->expects($this->once())
            ->method('get')
            ->with('id::_last_known')
            ->willReturn(7);

        $result = $this->subject->getLastPosition('id');
        $this->assertSame(7, $result);
    }

    public function testGetLastPositionWhenStartNotDefined(): void
    {
        $this->persistenceMock
            ->expects($this->once())
            ->method('get')
            ->with('id::_last_known')
            ->willReturn(false);

        $result = $this->subject->getLastPosition('id');
        $this->assertSame(0, $result);
    }

    public function testKeepCurrentPosition(): void
    {
        $this->persistenceMock
            ->expects($this->once())
            ->method('set')
            ->with('id::_last_known', 2);

        $this->subject->keepCurrentPosition('id', 2);
    }
}
