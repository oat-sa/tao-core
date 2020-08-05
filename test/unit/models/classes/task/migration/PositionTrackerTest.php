<?php declare(strict_types=1);

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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */


namespace oat\tao\test\unit\models\classes\task\migration;

use common_persistence_KeyValuePersistence;
use oat\generis\persistence\PersistenceManager;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\task\migration\service\PositionTracker;

class PositionTrackerTest extends TestCase
{
    /**
     * @var PositionTracker
     */
    private $subject;

    /**
     * @var PersistenceManager|MockObject
     */
    private $persistenceManagerMock;

    /**
     * @var common_persistence_KeyValuePersistence|MockObject
     */
    private $persistenceMock;

    public function setUp(): void
    {
        $this->subject = new PositionTracker();
        $this->persistenceManagerMock = $this->createMock(PersistenceManager::class);
        $this->persistenceMock = $this->createMock(common_persistence_KeyValuePersistence::class);

        $this->persistenceManagerMock
            ->method('getPersistenceById')
            ->willReturn($this->persistenceMock);

        $this->subject->setServiceLocator($this->getServiceLocatorMock(
            [
                PersistenceManager::SERVICE_ID => $this->persistenceManagerMock,
            ]
        ));
    }

    public function testGetLastPositionWhenStartDefined():void
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
