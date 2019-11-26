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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\test\unit\model\taskQueue\Task;

use oat\generis\test\TestCase;
use oat\tao\model\taskQueue\Task\AbstractTask;
use oat\tao\model\taskQueue\Task\TaskInterface;

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

    protected function tearDown(): void
    {
        $this->abstractTaskMock = null;
    }

    public function testSetMetadataShouldReturnTheTask(): void
    {
        $rs = $this->abstractTaskMock->setMetadata('foo', 'bar');
        $this->assertInstanceOf(TaskInterface::class, $rs);
    }

    public function testGetMetadataWhenKeyDoesNotExistThenReturnNull(): void
    {
        $this->assertNull($this->abstractTaskMock->getMetadata('not_existing_key'));
    }

    public function testGetMetadataWhenKeyDoesNotExistAndDefaultIsSuppliedThenReturnDefault(): void
    {
        $default = 'default_value';
        $this->assertSame($default, $this->abstractTaskMock->getMetadata('not_existing_key', $default));
    }

    public function testSetGetMetadataWhenKeyIsAString(): void
    {
        $key = 'key1';
        $value = 'value1';
        $this->abstractTaskMock->setMetadata($key, $value);

        $this->assertSame($value, $this->abstractTaskMock->getMetadata($key));
    }

    public function testSetGetMetadataWhenKeyIsAnArray(): void
    {
        $key = ['foo' => 'bar'];
        $this->abstractTaskMock->setMetadata($key);

        $this->assertSame('bar', $this->abstractTaskMock->getMetadata('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetMetadataWhenKeyIsInvalidThenThrowException(): void
    {
        $this->abstractTaskMock->setMetadata(new \stdClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetMetadataWhenKeyIsInvalidThenThrowException(): void
    {
        $this->abstractTaskMock->getMetadata(new \stdClass());
    }

    public function testSetParameterShouldReturnTheTask(): void
    {
        $rs = $this->abstractTaskMock->setMetadata('foo', 'bar');
        $this->assertInstanceOf(TaskInterface::class, $rs);
    }

    public function testGetParameterWhenKeyDoesNotExistThenReturnNull(): void
    {
        $this->assertNull($this->abstractTaskMock->getParameter('not_existing_key'));
    }

    public function testGetParameterWhenKeyDoesNotExistAndDefaultIsSuppliedThenReturnDefault(): void
    {
        $default = 'default_value';
        $this->assertSame($default, $this->abstractTaskMock->getParameter('not_existing_key', $default));
    }

    public function testSetGetParameterWhenKeyIsAString(): void
    {
        $key = 'key1';
        $value = 'value1';
        $this->abstractTaskMock->setParameter($key, $value);

        $this->assertSame($value, $this->abstractTaskMock->getParameter($key));
    }

    public function testSetGetParameterWhenKeyIsAnArray(): void
    {
        $key = ['foo' => 'bar'];
        $this->abstractTaskMock->setParameter($key);

        $this->assertSame('bar', $this->abstractTaskMock->getParameter('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetParameterWhenKeyIsInvalidThenThrowException(): void
    {
        $this->abstractTaskMock->setParameter(new \stdClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetParameterWhenKeyIsInvalidThenThrowException(): void
    {
        $this->abstractTaskMock->getParameter(new \stdClass());
    }

    public function testGetParametersShouldGiveBackTheWholeParameterContainer(): void
    {
        $this->abstractTaskMock->setParameter('key1', 'value1');
        $this->abstractTaskMock->setParameter('key2', 'value2');
        $this->abstractTaskMock->setParameter(['key3' => 'value3', 'key4' => 'value4']);

        $this->assertSame([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
            'key4' => 'value4',
        ], $this->abstractTaskMock->getParameters());
    }

    public function testIdShouldBeGeneratedInConstructor(): void
    {
        $this->assertSame($this->fakeId, $this->abstractTaskMock->getId());
    }

    public function testIdShouldNotBeOverWrittenBySetMetadata(): void
    {
        $this->abstractTaskMock->setMetadata('id', 4444);
        $this->assertSame($this->fakeId, $this->abstractTaskMock->getId());
    }

    public function testCreatedAtShouldBeGeneratedInConstructor(): void
    {
        $this->assertInstanceOf(\DateTime::class, $this->abstractTaskMock->getCreatedAt());
    }

    public function testSetCreatedAtShouldReturnTheTask(): void
    {
        $rs = $this->abstractTaskMock->setCreatedAt(new \DateTime());
        $this->assertInstanceOf(TaskInterface::class, $rs);
    }

    public function testSetGetCreatedAtWhenCreatedAtSetFromOutside(): void
    {
        $date = new \DateTime('yesterday 12:15:00');
        $this->abstractTaskMock->setCreatedAt($date);
        $this->assertSame($date, $this->abstractTaskMock->getCreatedAt());
    }

    public function testToStringWorks(): void
    {
        $this->assertSame('TASK ' . get_class($this->abstractTaskMock) . ' [' . $this->fakeId . ']', (string) $this->abstractTaskMock);
    }

    public function testOwnerShouldBeGeneratedInConstructor(): void
    {
        $this->assertSame($this->fakeOwner, $this->abstractTaskMock->getOwner());
    }

    public function testSetOwnerShouldReturnTheTask(): void
    {
        $rs = $this->abstractTaskMock->setOwner('owner');
        $this->assertInstanceOf(TaskInterface::class, $rs);
    }

    public function testSetGetOwnerWhenOwnerSetFromOutside(): void
    {
        $owner = 'example_owner';
        $this->abstractTaskMock->setOwner($owner);
        $this->assertSame($owner, $this->abstractTaskMock->getOwner());
    }

    public function testJsonSerializingAbstractTask(): void
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
                'key4' => 'value4',
            ],
            'parameters' => [
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3',
                'key4' => 'value4',
            ],
        ];

        $this->assertSame(json_encode($jsonArray), json_encode($this->abstractTaskMock));
    }

    public function testGetCreatedAtWhenJsonEncodeIsLoadedThenItShouldStillGiveADateTimeObject(): void
    {
        $date = new \DateTime('yesterday 12:15:00');
        $this->abstractTaskMock->setCreatedAt($date);

        $this->assertInstanceOf(\DateTime::class, $this->abstractTaskMock->getCreatedAt());
    }
}
