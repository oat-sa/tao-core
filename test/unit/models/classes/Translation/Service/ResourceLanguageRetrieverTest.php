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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Translation\Service;

use core_kernel_classes_Resource;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\Translation\Service\ResourceLanguageRetriever;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class ResourceLanguageRetrieverTest extends TestCase
{
    /** @var core_kernel_classes_Resource|MockObject */
    private core_kernel_classes_Resource $resource;

    /** @var stdClass|MockObject */
    private stdClass $retriever;

    /** @var UserLanguageServiceInterface|MockObject */
    private UserLanguageServiceInterface $userLanguageService;

    private ResourceLanguageRetriever $sut;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(core_kernel_classes_Resource::class);
        $this->retriever = $this
            ->getMockBuilder(stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();

        $this->userLanguageService = $this->createMock(UserLanguageServiceInterface::class);
        $this->sut = new ResourceLanguageRetriever($this->userLanguageService);
        $this->sut->setRetriever('resourceType', $this->retriever);
    }

    public function testRetrieveWithRetrieverForSpecificResourceType(): void
    {
        $this->resource
            ->expects($this->once())
            ->method('getRootId')
            ->willReturn('resourceType');

        $this->retriever
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->resource)
            ->willReturn('resourceLanguage');

        $this->userLanguageService
            ->expects($this->never())
            ->method('getDefaultLanguage');

        $this->assertEquals('resourceLanguage', $this->sut->retrieve($this->resource));
    }

    public function testRetrieveWithoutRetrieverForSpecificResourceType(): void
    {
        $this->resource
            ->expects($this->once())
            ->method('getRootId')
            ->willReturn('notRegisteredResourceType');

        $this->retriever
            ->expects($this->never())
            ->method('__invoke');

        $this->userLanguageService
            ->expects($this->once())
            ->method('getDefaultLanguage')
            ->willReturn('defaultLanguage');

        $this->assertEquals('defaultLanguage', $this->sut->retrieve($this->resource));
    }
}
