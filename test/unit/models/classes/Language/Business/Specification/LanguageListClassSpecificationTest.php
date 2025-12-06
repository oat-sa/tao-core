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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Language\Business\Specification;

use PHPUnit\Framework\TestCase;
use oat\tao\model\Language\Business\Specification\LanguageClassSpecification;
use PHPUnit\Framework\MockObject\MockObject;
use tao_models_classes_LanguageService;
use core_kernel_classes_Class;

class LanguageListClassSpecificationTest extends TestCase
{
    private const LANGUAGES_CLASS_URI = tao_models_classes_LanguageService::CLASS_URI_LANGUAGES;

    /** @var LanguageClassSpecification */
    private $sut;

    /** @var core_kernel_classes_Class|MockObject */
    private $class;

    protected function setUp(): void
    {
        $this->sut = new LanguageClassSpecification();

        $this->class = $this->createMock(core_kernel_classes_Class::class);
    }

    public function testIsSatisfiedByValid(): void
    {
        $this->class
            ->method('getUri')
            ->willReturn(self::LANGUAGES_CLASS_URI);

        $this->assertTrue($this->sut->isSatisfiedBy($this->class));
    }

    public function testIsSatisfiedByWithInvalidParentClass(): void
    {
        $this->class
            ->method('getUri')
            ->willReturn('Other URI');

        $this->assertFalse($this->sut->isSatisfiedBy($this->class));
    }
}
