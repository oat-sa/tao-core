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
 * Copyright (c) 2021-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\search;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use oat\tao\model\search\IdentifierSearcher;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\SearchQuery;

class IdentifierSearchTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const LOCAL_NAMESPACE = 'http://something';

    private IdentifierSearcher $subject;
    private Ontology|MockObject $ontology;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);

        $this->subject = new IdentifierSearcher();
        $this->subject->withLocalNamespace(self::LOCAL_NAMESPACE);
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    Ontology::SERVICE_ID => $this->ontology,
                ]
            )
        );
    }

    public function testSearchByNoUri(): void
    {
        $query = new SearchQuery(
            'user input',
            'rootClass',
            'parentClass',
            1,
            10,
            1
        );

        $this->assertEquals(new ResultSet([], 0), $this->subject->search($query));
    }

    public function testSearchByUri(): void
    {
        $uri = self::LOCAL_NAMESPACE;
        $label = 'My Resource label';

        $query = new SearchQuery(
            $uri,
            'rootClass',
            'parentClass',
            1,
            10,
            1
        );

        $resultSetMock = new ResultSet(
            [
                [
                    'id' => $uri,
                    'label' => $label,
                ],
            ],
            1
        );

        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $class = $this->createMock(core_kernel_classes_Class::class);

        $this->ontology
            ->method('getResource')
            ->willReturn($resource);

        $this->ontology
            ->method('getClass')
            ->willReturn($class);

        $resource
            ->method('exists')
            ->willReturn(true);

        $resource
            ->method('isInstanceOf')
            ->willReturn(true);

        $resource
            ->method('getUri')
            ->willReturn($uri);

        $resource
            ->method('getLabel')
            ->willReturn($label);

        $this->assertEquals($resultSetMock, $this->subject->search($query));
    }
}
