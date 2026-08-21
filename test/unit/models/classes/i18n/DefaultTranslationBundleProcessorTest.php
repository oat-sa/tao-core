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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\i18n;

use oat\tao\model\i18n\DefaultTranslationBundleProcessor;
use PHPUnit\Framework\TestCase;

class DefaultTranslationBundleProcessorTest extends TestCase
{
    private DefaultTranslationBundleProcessor $sut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = new DefaultTranslationBundleProcessor();
    }

    /**
     * @return iterable<string, array{0: ?string, 1: ?string}>
     */
    public static function convertRubyTagsProvider(): iterable
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', ''];
        yield 'plain text unchanged' => ['Hello', 'Hello'];
        yield 'ruby tags' => ['{ruby}漢{/ruby}', '<ruby>漢</ruby>'];
        yield 'rt tags' => ['{rt}かん{/rt}', '<rt>かん</rt>'];
        yield 'rp tags' => ['{rp}x{/rp}', '<rp>x</rp>'];
        yield 'rb tags' => ['{rb}字{/rb}', '<rb>字</rb>'];
        yield 'combined fragment' => [
            '{ruby}字{/ruby}{rt}じ{/rt}{rp}(じ){/rp}',
            '<ruby>字</ruby><rt>じ</rt><rp>(じ)</rp>',
        ];
    }

    /**
     * @dataProvider convertRubyTagsProvider
     */
    public function testConvertRubyTags(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, DefaultTranslationBundleProcessor::convertRubyTags($input));
    }

    public function testProcessConvertsMsgstrValuesOnlyAndPreservesKeys(): void
    {
        $input = [
            'Source {ruby}A{/ruby}' => 'Target {ruby}B{/ruby}',
            'Plain' => 'Still plain',
        ];

        $result = $this->sut->process($input, 'ja-JP');

        $this->assertSame(
            [
                'Source {ruby}A{/ruby}' => 'Target <ruby>B</ruby>',
                'Plain' => 'Still plain',
            ],
            $result
        );
    }

    public function testProcessLeavesNonStringValuesUnchanged(): void
    {
        $input = [
            'k' => 42,
        ];

        $result = $this->sut->process($input, 'en-US');

        $this->assertSame(['k' => 42], $result);
    }

    public function testProcessPassesLangCodeWithoutAlteringOutput(): void
    {
        $input = ['x' => '{ruby}y{/ruby}'];
        $this->assertSame(['x' => '<ruby>y</ruby>'], $this->sut->process($input, 'en-US'));
        $this->assertSame(['x' => '<ruby>y</ruby>'], $this->sut->process($input, 'ar-arb'));
    }
}
