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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\test\unit\model\taskQueue\Task;

use InvalidArgumentException;
use oat\tao\model\taskQueue\Task\AbstractTask;
use oat\tao\model\taskQueue\Task\TaskInterface;
use PHPUnit\Framework\TestCase;

class AbstractTaskTest extends TestCase
{
    /**
     * @var AbstractTask
     */
    private $abstractTaskMock;
    private $fakeId = 'ADFA23234sdfsdf';
    private $fakeOwner = 'FakeOwner';

    protected function setUp(): void
    {
        $this->abstractTaskMock = $this->getMockBuilder(AbstractTask::class)
            ->setConstructorArgs([$this->fakeId, $this->fakeOwner])
            ->getMockForAbstractClass();
    }

    public function tearDown(): void
    {
        $this->abstractTaskMock = null;
    }

    public function testSetMetadataShouldReturnTheTask()
    {
        $rs = $this->abstractTaskMock->setMetadata('foo', 'bar');
        $this->assertInstanceOf(TaskInterface::class, $rs);
    }

    public function testGetMetadataWhenKeyDoesNotExistThenReturnNull()
    {
        $this->assertNull($this->abstractTaskMock->getMetadata('not_existing_key'));
    }

    public function testGetMetadataWhenKeyDoesNotExistAndDefaultIsSuppliedThenReturnDefault()
    {
        $default = 'default_value';
        $this->assertEquals($default, $this->abstractTaskMock->getMetadata('not_existing_key', $default));
    }

    public function testSetGetMetadataWhenKeyIsAString()
    {
        $key = 'key1';
        $value = 'value1';
        $this->abstractTaskMock->setMetadata($key, $value);

        $this->assertEquals($value, $this->abstractTaskMock->getMetadata($key));
    }

    public function testSetGetMetadataWhenKeyIsAnArray()
    {
        $key = ['foo' => 'bar'];
        $this->abstractTaskMock->setMetadata($key);

        $this->assertEquals('bar', $this->abstractTaskMock->getMetadata('foo'));
    }

    public function testSetMetadataWhenKeyIsInvalidThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->abstractTaskMock->setMetadata(new \stdClass());
    }

    public function testGetMetadataWhenKeyIsInvalidThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->abstractTaskMock->getMetadata(new \stdClass());
    }

    public function testSetParameterShouldReturnTheTask()
    {
        $rs = $this->abstractTaskMock->setMetadata('foo', 'bar');
        $this->assertInstanceOf(TaskInterface::class, $rs);
    }

    public function testGetParameterWhenKeyDoesNotExistThenReturnNull()
    {
        $this->assertNull($this->abstractTaskMock->getParameter('not_existing_key'));
    }

    public function testGetParameterWhenKeyDoesNotExistAndDefaultIsSuppliedThenReturnDefault()
    {
        $default = 'default_value';
        $this->assertEquals($default, $this->abstractTaskMock->getParameter('not_existing_key', $default));
    }

    public function testSetGetParameterWhenKeyIsAString()
    {
        $key = 'key1';
        $value = 'value1';
        $this->abstractTaskMock->setParameter($key, $value);

        $this->assertEquals($value, $this->abstractTaskMock->getParameter($key));
    }

    public function testSetGetParameterWhenKeyIsAnArray()
    {
        $key = ['foo' => 'bar'];
        $this->abstractTaskMock->setParameter($key);

        $this->assertEquals('bar', $this->abstractTaskMock->getParameter('foo'));
    }

    public function testSetParameterWhenKeyIsInvalidThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->abstractTaskMock->setParameter(new \stdClass());
    }

    public function testGetParameterWhenKeyIsInvalidThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->abstractTaskMock->getParameter(new \stdClass());
    }

    public function testGetParametersShouldGiveBackTheWholeParameterContainer()
    {
        $this->abstractTaskMock->setParameter('key1', 'value1');
        $this->abstractTaskMock->setParameter('key2', 'value2');
        $this->abstractTaskMock->setParameter(['key3' => 'value3', 'key4' => 'value4']);

        $this->assertEquals([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
            'key4' => 'value4'
        ], $this->abstractTaskMock->getParameters());
    }

    public function testIdShouldBeGeneratedInConstructor()
    {
        $this->assertEquals($this->fakeId, $this->abstractTaskMock->getId());
    }

    public function testIdShouldNotBeOverWrittenBySetMetadata()
    {
        $this->abstractTaskMock->setMetadata('id', 4444);
        $this->assertEquals($this->fakeId, $this->abstractTaskMock->getId());
    }

    public function testCreatedAtShouldBeGeneratedInConstructor()
    {
        $this->assertInstanceOf(\DateTime::class, $this->abstractTaskMock->getCreatedAt());
    }

    public function testSetCreatedAtShouldReturnTheTask()
    {
        $rs = $this->abstractTaskMock->setCreatedAt(new \DateTime());
        $this->assertInstanceOf(TaskInterface::class, $rs);
    }

    public function testSetGetCreatedAtWhenCreatedAtSetFromOutside()
    {
        $date = new \DateTime('yesterday 12:15:00');
        $this->abstractTaskMock->setCreatedAt($date);
        $this->assertEquals($date, $this->abstractTaskMock->getCreatedAt());
    }

    public function testToStringWorks()
    {
        $this->assertEquals(
            'TASK ' . get_class($this->abstractTaskMock) . ' [' . $this->fakeId . ']',
            (string) $this->abstractTaskMock
        );
    }

    public function testOwnerShouldBeGeneratedInConstructor()
    {
        $this->assertEquals($this->fakeOwner, $this->abstractTaskMock->getOwner());
    }

    public function testSetOwnerShouldReturnTheTask()
    {
        $rs = $this->abstractTaskMock->setOwner('owner');
        $this->assertInstanceOf(TaskInterface::class, $rs);
    }

    public function testSetGetOwnerWhenOwnerSetFromOutside()
    {
        $owner = 'example_owner';
        $this->abstractTaskMock->setOwner($owner);
        $this->assertEquals($owner, $this->abstractTaskMock->getOwner());
    }

    public function testJsonSerializingAbstractTask()
    {
        $this->abstractTaskMock->setMetadata('key1', 'value1');
        $this->abstractTaskMock->setMetadata('key2', 'value2');
        $this->abstractTaskMock->setMetadata(['key3' => 'value3', 'key4' => 'value4']);

        $this->abstractTaskMock->setParameter('key1', 'value1');
        $this->abstractTaskMock->setParameter('key2', 'value2');
        $this->abstractTaskMock->setParameter(['key3' => 'value3', 'key4' => 'value4']);

        $date = new \DateTime('-2days 12:15:00');
        $this->abstractTaskMock->setCreatedAt($date);

        $jsonArray = [
            'taskFqcn' => get_class($this->abstractTaskMock),
            'metadata' => [
                '__id__' => $this->fakeId,
                '__created_at__' => $date->format('c'),
                '__owner__' => $this->fakeOwner,
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3',
                'key4' => 'value4'
            ],
            'parameters' => [
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3',
                'key4' => 'value4'
            ]
        ];

        $this->assertEquals(json_encode($jsonArray), json_encode($this->abstractTaskMock));
    }

    public function testGetCreatedAtWhenJsonEncodeIsLoadedThenItShouldStillGiveADateTimeObject()
    {
        $date = new \DateTime('yesterday 12:15:00');
        $this->abstractTaskMock->setCreatedAt($date);

        $this->assertInstanceOf(\DateTime::class, $this->abstractTaskMock->getCreatedAt());
    }
}
