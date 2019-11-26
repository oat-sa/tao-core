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
 * Copyright (c) 2013 Open Assessment Technologies S.A.
 */

namespace  oat\tao\test\integration\lock;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\lock\implementation\OntoLock;
use oat\tao\model\TaoOntology;
use oat\tao\test\TaoPhpUnitTestRunner;

/**
 * Test the lock implemented as a property in the ontology
 *
 * @author plichart
 * @package tao
 */
class OntoLockTest extends TaoPhpUnitTestRunner
{
    protected $tempResource = null;

    protected $ontoLock = null;

    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp(): void
    {
        $resourceClass = new core_kernel_classes_Class(OntologyRdfs::RDFS_RESOURCE);
        $this->tempResource = $resourceClass->createInstance('MyTest');
        $this->ontoLock = new OntoLock();

        $this->owner = new core_kernel_classes_Resource('#virtualOwner');
    }

    /**
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        $this->tempResource->delete();
    }

    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testSetLock(): void
    {
        $this->assertFalse($this->ontoLock->isLocked($this->tempResource));
        $this->ontoLock->setLock($this->tempResource, $this->owner);
        $this->assertTrue($this->ontoLock->isLocked($this->tempResource));
        $other = new core_kernel_classes_Resource('#other');
        // setting a lock while it is locked should return an exception
        try {
            $this->ontoLock->setLock($this->tempResource, $other->getUri());
            $this->fail('should have throw a exception');
        } catch (\common_Exception $e) {
            $this->assertInstanceOf('oat\tao\model\lock\ResourceLockedException', $e);
        }
    }

    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testReleaseLock(): void
    {
        $this->assertFalse($this->ontoLock->isLocked($this->tempResource));
        $this->assertFalse($this->ontoLock->releaseLock($this->tempResource, $this->owner->getUri()));

        $this->ontoLock->setLock($this->tempResource, $this->owner->getUri());
        $this->assertTrue($this->ontoLock->isLocked($this->tempResource));
        $this->ontoLock->releaseLock($this->tempResource, $this->owner->getUri());
        $this->assertFalse($this->ontoLock->isLocked($this->tempResource));

        $this->ontoLock->setLock($this->tempResource, $this->owner->getUri());
        $other = new core_kernel_classes_Resource('#other');
        try {
            $this->ontoLock->releaseLock($this->tempResource, $other->getUri());
            $this->fail('should have throw a exception');
        } catch (\common_Exception $e) {
            $this->assertInstanceOf('\common_exception_Unauthorized', $e);
        }
    }

    /**
     * @expectedException common_exception_InconsistentData
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testReleaseLockException(): void
    {
        $resource = $this->prophesize('core_kernel_classes_Resource');

        $lockProp = new core_kernel_classes_Property(TaoOntology::PROPERTY_LOCK);
        $resource->getPropertyValues($lockProp)->willReturn(['foo', 'bar']);
        $this->ontoLock->releaseLock($resource->reveal(), $this->owner->getUri());
    }

    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testForceReleaseLock(): void
    {
        $resource = $this->prophesize('core_kernel_classes_Resource');
        $lockProp = new core_kernel_classes_Property(TaoOntology::PROPERTY_LOCK);

        $this->ontoLock->forceReleaseLock($resource->reveal());

        $resource->removePropertyValues($lockProp)->shouldHaveBeenCalled();
    }
}
