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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\search;

use oat\generis\test\TestCase;
use oat\tao\model\search\ElasticSearchBridge;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\Search;
use oat\tao\model\search\SearchQuery;

class ElasticSearchBridgeTest extends TestCase
{
    /** @var ElasticSearchBridge */
    private $subject;

    private $searchEngineMock;

    public function setUp(): void
    {
        $this->searchEngineMock = $this->createMock(Search::class);

        $this->subject = new ElasticSearchBridge();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    Search::SERVICE_ID => $this->searchEngineMock,
                ]
            )
        );
    }

    public function testSearch(): void
    {
        $query = new SearchQuery('user input',
            'rootClass',
            'parentClass',
            1,
            10,
            1);

        $resultSetMock = $this->createMock(ResultSet::class);

        $this->searchEngineMock
            ->expects($this->once())
            ->method('query')
            ->with(
                'user input AND parent_classes: "parentClass"',
                'rootClass',
                1,
                10
            )
            ->willReturn($resultSetMock);


        $result = $this->subject->search($query);
        $this->assertEquals($resultSetMock, $result);
    }
}
