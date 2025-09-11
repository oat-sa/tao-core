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

use oat\tao\model\taskQueue\Task\CallbackTask;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\test\Asset\CallableFixture;
use PHPUnit\Framework\TestCase;

class CallbackTaskTest extends TestCase
{
    /**
     * @var CallbackTask
     */
    private $task;
    private $fakeId = 'WCDWW544eefdtyh';
    private $fakeOwner = 'FakeOwner2';

    protected function setUp(): void
    {
        $this->task = new CallbackTask($this->fakeId, $this->fakeOwner);
    }

    public function tearDown(): void
    {
        $this->task = null;
    }

    public function testSetCallableShouldReturnTheTask()
    {
        $rs = $this->task->setCallable('abs');
        $this->assertInstanceOf(TaskInterface::class, $rs);
        $this->assertInstanceOf(CallbackTaskInterface::class, $rs);
    }

    public function testSetGetCallableShouldAcceptStaticClassMethodCall()
    {
        $callable = [CallableFixture::class, 'exampleStatic'];
        $this->task->setCallable($callable);
        $this->assertEquals($callable, $this->task->getCallable());
    }

    public function testSetGetCallableShouldAcceptObjectsImplementingInvoke()
    {
        $callable = new CallableFixture();
        $this->task->setCallable($callable);
        $this->assertEquals($callable, $this->task->getCallable());
    }

    /**
     * @dataProvider provideCallables
     */
    public function testInvokeWithCallables($callable, $expected)
    {
        $this->task->setCallable($callable);
        $this->task->setParameter($expected);
        // every methods of CallableFixture double will just return the given parameters
        $this->assertEquals($expected, $this->task->__invoke());
    }

    public function provideCallables()
    {
        return [
            'WithStaticClass' => [
                [CallableFixture::class, 'exampleStatic'],
                ['param1' => 'value1']
            ],
            'ClassWithInvoke' => [
                new CallableFixture(),
                ['param2' => 'value2']
            ]
        ];
    }

    public function testMarkAsEnqueuedAndIsEnqueued()
    {
        $this->task->markAsEnqueued();
        $this->assertTrue($this->task->isEnqueued());
    }

    public function testJsonSerializingCallbackTask()
    {
        $this->task->setCallable(new CallableFixture());

        $date = new \DateTime('-2days 12:15:00');
        $this->task->setCreatedAt($date);

        $this->task->setParameter('key1', 'value1');

        $jsonArray = [
            'taskFqcn' => get_class($this->task),
            'metadata' => [
                '__id__' => $this->fakeId,
                '__created_at__' => $date->format('c'),
                '__owner__' => $this->fakeOwner,
                '__callable__' => CallableFixture::class
            ],
            'parameters' => [
                'key1' => 'value1'
            ]
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($jsonArray), json_encode($this->task));
    }

    public function testGetCallableShouldReturnStringAfterJsonDecode()
    {
        $json = '{"taskFqcn":"oat\\\oatbox\\\TaskQueue\\\CallbackTask","metadata":{"__callable__":'
            . '"oat\\\tao\\\test\\\Asset\\\CallableFixture"},"parameters":{"key1":"value1"}}';
        $data = json_decode($json, true);

        $this->task->setMetadata($data['metadata']);

        $this->assertEquals(CallableFixture::class, $this->task->getCallable());
    }
}
