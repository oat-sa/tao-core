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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\theme;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ThemeServiceAbstractTest extends TestCase
{
    /** @var ThemeServiceAbstract|MockObject */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = $this->getMockForAbstractClass(
            ThemeServiceAbstract::class,
            [],
            '',
            false,
            true,
            true,
            [
                'getThemeById',
                'getOption',
            ]
        );
    }

    public function testGetFirstThemeIdByLanguage(): void
    {
        $templateId = 'my_template';
        $language = 'en-US';
        $theme = $this->createMock(ThemeMock::class);

        $this->subject
            ->method('getThemeById')
            ->with($templateId)
            ->willReturn($theme);

        $this->subject
            ->method('getOption')
            ->with(ThemeServiceInterface::OPTION_AVAILABLE)
            ->willReturn(
                [
                    $templateId => null,
                ]
            );

        $theme->method('supportsLanguage')
            ->with($language)
            ->willReturn(true);

        $this->assertSame($templateId, $this->subject->getFirstThemeIdByLanguage($language));
    }

    public function testGetFirstThemeIdByLanguageWillReturnNullIfThemeIsNotSupported(): void
    {
        $templateId = 'my_template_not_supported';
        $language = 'en-US';
        $theme = $this->createMock(ThemeMock::class);

        $this->subject
            ->method('getThemeById')
            ->with($templateId)
            ->willReturn($theme);

        $this->subject
            ->method('getOption')
            ->with(ThemeServiceInterface::OPTION_AVAILABLE)
            ->willReturn(
                [
                    $templateId => null,
                ]
            );

        $theme->method('supportsLanguage')
            ->with($language)
            ->willReturn(false);

        $this->assertNull($this->subject->getFirstThemeIdByLanguage($language));
    }
}

interface ThemeMock extends Theme, LanguageAwareTheme
{
}
