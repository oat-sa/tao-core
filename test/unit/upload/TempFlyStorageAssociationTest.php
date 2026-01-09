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
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\tao\test\unit\upload;

use common_persistence_InMemoryKvDriver;
use common_persistence_KeyValuePersistence;
use oat\generis\persistence\PersistenceManager;
use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\filesystem\File;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\upload\TempFlyStorageAssociation;
use PHPUnit\Framework\TestCase;

/**
 * Class TempFlyStorageAssociationTest
 * @package oat\tao\test\unit\upload
 */
class TempFlyStorageAssociationTest extends TestCase
{
    use ServiceManagerMockTrait;

    public function testSetUpload(): void
    {
        $file = new File('foo', 'bar');
        $storage = $this->getInstance();
        $storage->setUpload($file);
        $storage->addLocalCopies($file, 'baz');
        $this->assertTrue(in_array('baz', $storage->getLocalCopies($file)));
    }

    public function testAddLocalCopies(): void
    {
        $file = new File('foo', 'bar');
        $storage = $this->getInstance();
        $storage->setUpload($file);
        $storage->addLocalCopies($file, 'baz');
        $storage->addLocalCopies($file, 'qux');
        $this->assertTrue(in_array('baz', $storage->getLocalCopies($file)));
        $this->assertTrue(in_array('qux', $storage->getLocalCopies($file)));
        $this->assertCount(2, $storage->getLocalCopies($file));
    }

    public function testGetLocalCopies(): void
    {
        $file = new File('foo', 'bar');
        $storage = $this->getInstance();
        $storage->setUpload($file);
        $this->assertEquals([], $storage->getLocalCopies($file));
        $storage->addLocalCopies($file, 'baz');
        $storage->addLocalCopies($file, 'qux');
        $this->assertTrue(in_array('baz', $storage->getLocalCopies($file)));
        $this->assertTrue(in_array('qux', $storage->getLocalCopies($file)));
        $this->assertCount(2, $storage->getLocalCopies($file));
    }

    public function testRemoveFiles(): void
    {
        $file = new File('foo', 'bar');
        $storage = $this->getInstance();
        $storage->setUpload($file);
        $storage->addLocalCopies($file, 'baz');
        $this->assertTrue(in_array('baz', $storage->getLocalCopies($file)));
        $storage->removeFiles($file);
        $this->assertEquals([], $storage->getLocalCopies($file));
    }

    private function getInstance(): TempFlyStorageAssociation
    {
        $persistenceManagerMock = $this->createMock(PersistenceManager::class);
        $persistenceManagerMock
            ->method('setServiceLocator')
            ->willReturn(null);
        $persistenceManagerMock
            ->method('getPersistenceById')
            ->with('default_kv')
            ->willReturn(new common_persistence_KeyValuePersistence([], new common_persistence_InMemoryKvDriver()));

        $serviceManagerMock = $this->getServiceManagerMock([
            PersistenceManager::SERVICE_ID => $persistenceManagerMock,
        ]);

        return new TempFlyStorageAssociation($serviceManagerMock);
    }
}
