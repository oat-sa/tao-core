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

namespace oat\tao\test\unit\models\classes\menu;

use LogicException;
use oat\tao\model\menu\ExcludedSectionListProviderInterface;
use oat\tao\model\menu\SectionVisibilityFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SectionVisibilityFilterTest extends TestCase
{

    /** @var SectionVisibilityFilter */
    private $subject;

    /** @var ExcludedSectionListProviderInterface|MockObject */
    private $excludedSectionListProvider;

    public function setUp(): void
    {
        $this->excludedSectionListProvider = $this->createMock(ExcludedSectionListProviderInterface::class);
        $this->subject = new SectionVisibilityFilter(
            [
                SectionVisibilityFilter::EXCLUDED_SECTION_LIST_PROVIDERS => [
                    $this->excludedSectionListProvider,
                ],
            ]
        );
    }

    public function testIsHidden(): void
    {
        $this->excludedSectionListProvider
            ->expects(self::once())
            ->method('getExcludedSections')
            ->willReturn(['existingSection']);

        self::assertTrue($this->subject->isHidden('existingSection'));
    }

    public function testIsHiddenWithEmptyResult(): void
    {
        $this->excludedSectionListProvider
            ->expects(self::once())
            ->method('getExcludedSections')
            ->willReturn([]);

        self::assertFalse($this->subject->isHidden('existingSection'));
    }

    public function testExceptionWhenWrongClassInjected(): void
    {
        $this->expectException(LogicException::class);

        $subject = new SectionVisibilityFilter(
            [
                SectionVisibilityFilter::EXCLUDED_SECTION_LIST_PROVIDERS => [
                    new class {},
                ],
            ]
        );

        $subject->isHidden('foo');
    }

}
