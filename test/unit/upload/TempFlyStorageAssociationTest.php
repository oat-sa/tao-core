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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\test\unit\upload;

use oat\generis\persistence\PersistenceManager;
use oat\generis\test\TestCase;
use oat\oatbox\filesystem\File;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\upload\TempFlyStorageAssociation;
use oat\tao\model\upload\TmpLocalAwareStorageInterface;
use Prophecy\Argument;

/**
 * Class TempFlyStorageAssociationTest
 * @package oat\tao\test\unit\upload
 */
class TempFlyStorageAssociationTest extends TestCase
{

    public function testSetUpload()
    {
        $file = new File('foo', 'bar');
        $storage = $this->getInstance();
        $storage->setUpload($file);
        $storage->addLocalCopies($file, 'baz');
        $this->assertTrue(in_array('baz', $storage->getLocalCopies($file)));
    }

    public function testAddLocalCopies()
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

    public function testGetLocalCopies()
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

    public function testRemoveFiles()
    {
        $file = new File('foo', 'bar');
        $storage = $this->getInstance();
        $storage->setUpload($file);
        $storage->addLocalCopies($file, 'baz');
        $this->assertTrue(in_array('baz', $storage->getLocalCopies($file)));
        $storage->removeFiles($file);
        $this->assertEquals([], $storage->getLocalCopies($file));
    }

    /**
     * @return TmpLocalAwareStorageInterface
     * @throws \common_Exception
     */
    private function getInstance()
    {
        /** @var TempFlyStorageAssociation $instance */
        return new TempFlyStorageAssociation($this->getServiceManagerMock());
    }

    /**
     * @return ServiceManager
     * @throws \common_Exception
     */
    private function getServiceManagerMock()
    {
        $config = new \common_persistence_KeyValuePersistence([], new \common_persistence_InMemoryKvDriver());
        $serviceManager = new ServiceManager($config);

        $pmProphecy = $this->prophesize(PersistenceManager::class);
        $pmProphecy->setServiceLocator(Argument::any())->willReturn(null);
        $pmProphecy->getPersistenceById('default_kv')
            ->willReturn(new \common_persistence_KeyValuePersistence([], new \common_persistence_InMemoryKvDriver()));
        $pm = $pmProphecy->reveal();

        $serviceManager->register(PersistenceManager::SERVICE_ID, $pm);

        return $serviceManager;
    }
}
