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
 * Copyright (c) 2013 Open Assessment Technologies S.A.
 */
use oat\tao\test\TaoPhpUnitTestRunner;

include_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 * Test the lock implemented as a property in the ontology
 * 
 * @author plichart
 * @package tao
 
 */
class DbLockTest extends TaoPhpUnitTestRunner {
	
    protected $tempResource = null;
    protected $lockService = null;
    
    public function setUp() {
        $this->lockService = tao_models_classes_lock_DbLock::singleton();
        $this->lockService->setEnabled(true);
    }

    /**
     * verify lock class
     * 
     * @return void
     */
    public function testService()
    {
        $this->assertIsA($this->lockService, 'tao_models_classes_lock_DbLock');
    }

    /**
     * create instance
     * 
     * @return \core_kernel_classes_Resource
     */
    public function testCreateInstance()
    {
        $resourceClass = new core_kernel_classes_Class(RDFS_RESOURCE);
        $this->assertInstanceOf('core_kernel_classes_Class', $resourceClass);

        $instance = $resourceClass->createInstance('MyTest');
        $this->assertInstanceOf('core_kernel_classes_Resource', $instance);

        return $instance;
    }

    /**
     * test lock validate
     * @depends testCreateInstance
     * 
     * @param $instance
     * @return \core_kernel_classes_Resource
     */
	public function testSetLock($instance){
        $this->assertFalse($this->lockService->isLocked($instance));

        $owner = new core_kernel_classes_Resource('#virtualOwner');
        $this->assertInstanceOf('core_kernel_classes_Resource', $owner);

        $this->lockService->setLock($instance, $owner);
        $this->assertTrue($this->lockService->isLocked($instance));

        return $owner;
	}

    /**
     * test lock while already locked
     * @depends testCreateInstance
     * @depends testSetLock
     * 
     * @param $instance
     * @param $owner
     * 
     * @expectedException \common_Exception
     * @return void
     */
	public function testLockException($instance, $owner){
        $this->lockService->setLock($instance, $owner);
	}

    /**
     * test getLockData
     * @depends testCreateInstance
     * @depends testSetLock
     * 
     * @param $instance
     * @param $owner
     * 
     * @return void
     */
    public function testGetLockData($instance, $owner){
        $lockData = $this->lockService->getLockData($instance);

        $this->assertNotEmpty($lockData);

        $this->assertEquals(
            $owner->getUri(),
            $lockData->getOwner()->getUri()
        );
        $this->assertEquals(
            $lockData->getResource()->getUri(),
            $instance->getUri()
        );

    }

    /**
     * test lock while already locked
     * @depends testCreateInstance
     * @depends testSetLock
     * 
     * @param $instance
     * @param $owner
     * 
     * @return void
     */
    public function testReleaseLock($instance, $owner){
        $this->assertTrue($this->lockService->isLocked($instance));
        $this->lockService->releaseLock($instance, $owner);
        $this->assertFalse($this->lockService->isLocked($instance));
    }

    /**
     * test forceReleaseLock
     * @depends testCreateInstance
     * @depends testSetLock
     * 
     * @param $instance
     * @param $owner
     * 
     * @return void
     */
    public function testForceReleaseLock($instance, $owner){
        $this->assertFalse($this->lockService->isLocked($instance));
        $this->lockService->setLock($instance, $owner);
        $this->assertTrue($this->lockService->isLocked($instance));
        $this->lockService->forceReleaseLock($instance, $owner);
        $this->assertFalse($this->lockService->isLocked($instance));
    }
}
?>