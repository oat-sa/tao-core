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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\helpers\test\unit\helpers\form\Factory;

use oat\generis\test\TestCase;
use oat\tao\helpers\form\Factory\ElementPropertyListValuesFactory;
use oat\tao\model\Specification\ClassSpecificationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use tao_models_classes_ListService;

class ElementPropertyListValuesFactoryTest extends TestCase
{
    /** @var ElementPropertyListValuesFactory */
    private $sut;

    /** @var ClassSpecificationInterface|MockObject */
    private $remoteListClassSpecification;

    /** @var MockObject|tao_models_classes_ListService */
    private $listService;

    public function setUp(): void
    {
        $this->remoteListClassSpecification = $this->createMock(ClassSpecificationInterface::class);
        $this->listService = $this->createMock(tao_models_classes_ListService::class);

        $this->sut = new ElementPropertyListValuesFactory(
            $this->remoteListClassSpecification,
            $this->listService
        );
    }

    public function testCreate(): void
    {
        $this->markTestIncomplete('TODO');
    }
}
