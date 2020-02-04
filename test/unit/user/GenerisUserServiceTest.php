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
 * Copyright (c) 2020  (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\models\classes\service;

use oat\tao\model\search\Search;
use oat\tao\model\search\strategy\GenerisSearch;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\GenerisUserService;
use oat\generis\test\TestCase;

class GenerisUserServiceTest extends TestCase
{

    public function testFindUser()
    {
        $searchString = 'test';
        $testId = 'testId';
        $testUser = 'testUser';
        $searchServiceMock = $this->createMock(GenerisSearch::class);
        $searchServiceMock->expects($this->once())
            ->method('query')
            ->with($searchString, TaoOntology::CLASS_URI_TAO_USER)
            ->willReturn([$testId]);
        $serviceLocator = $this->getServiceLocatorMock([Search::SERVICE_ID => $searchServiceMock]);
        $generisUserServiceMock = $this->getMockBuilder(GenerisUserService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();
        $generisUserServiceMock->expects($this->once())
            ->method('getUser')
            ->with($testId)
            ->willReturn($testUser);
        $generisUserServiceMock->setServiceLocator($serviceLocator);
        $this->assertSame([$testId => $testUser], $generisUserServiceMock->findUser($searchString));
    }
}
