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

namespace oat\tao\test\unit\model\resources\relation\service;

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\resources\relation\FindAllQuery;
use oat\tao\model\resources\relation\ResourceRelation;
use oat\tao\model\resources\relation\ResourceRelationCollection;
use oat\tao\model\resources\relation\service\ResourceRelationServiceInterface;
use oat\tao\model\resources\relation\service\ResourceRelationServiceProxy;
use PHPUnit\Framework\MockObject\MockObject;

class ResourceRelationServiceProxyTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ResourceRelationServiceProxy $subject;
    private ResourceRelationServiceInterface|MockObject $resourceRelationService;

    protected function setUp(): void
    {
        $this->resourceRelationService = $this->createMock(ResourceRelationServiceInterface::class);
        $this->subject = new ResourceRelationServiceProxy();
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    'serviceId' => $this->resourceRelationService,
                ]
            )
        );
    }

    public function testGetRelations(): void
    {
        $relation = new ResourceRelation(
            'item',
            'id',
            'label'
        );
        $collection = new ResourceRelationCollection(...[$relation]);
        $query = new FindAllQuery('itemId', null, 'item');

        $this->subject->addService('item', 'serviceId');
        $this->subject->addService('media', 'anotherServiceId');

        $this->resourceRelationService
            ->method('findRelations')
            ->with($query)
            ->willReturn($collection);

        $iterator = $this->subject->findRelations($query)->getIterator();

        $this->assertCount(1, $iterator->getArrayCopy());
        $this->assertSame($relation, $iterator->offsetGet(0));
    }

    public function testGetRelationsWillBeEmptyIfThereIsNoServiceMapped(): void
    {
        $query = new FindAllQuery('itemId', null, 'item');

        $this->subject->addService('media', 'anotherServiceId');

        $this->assertCount(0, $this->subject->findRelations($query)->getIterator()->getArrayCopy());
    }

    public function testAddAndRemoveService(): void
    {
        $this->subject->addService('media', 'service1');
        $this->subject->addService('media', 'service2');
        $this->subject->addService('item', 'service3');
        $this->subject->addService('item', 'service4');

        $this->assertSame(
            [
                'service1',
                'service2',
            ],
            $this->subject->getOption(ResourceRelationServiceProxy::OPTION_SERVICES)['media']
        );
        $this->assertSame(
            [
                'service3',
                'service4',
            ],
            $this->subject->getOption(ResourceRelationServiceProxy::OPTION_SERVICES)['item']
        );

        $this->subject->removeService('media', 'service2');
        $this->subject->removeService('item', 'service4');

        $this->assertSame(
            [
                'service1',
            ],
            $this->subject->getOption(ResourceRelationServiceProxy::OPTION_SERVICES)['media']
        );
        $this->assertSame(
            [
                'service3',
            ],
            $this->subject->getOption(ResourceRelationServiceProxy::OPTION_SERVICES)['item']
        );
    }
}
