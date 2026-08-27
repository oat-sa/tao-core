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
 * @category  TAO
 * @package   Tao
 * @author    Open Assessment Technologies SA <contact@taotesting.com>
 * @license   GPL-2.0-only https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 * @link      https://www.taotesting.com
 */

namespace oat\tao\test\unit\translation;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Unit tests for template-aware source extraction helpers.
 */
class SourceCodeExtractorTest extends TestCase
{
    /**
     * Ignore plural helper calls when collecting singular phrases.
     *
     * @return void
     */
    public function testSingularPhraseExtractionSkipsExplicitPluralCalls()
    {
        $extractor = new \tao_helpers_translation_SourceCodeExtractor('', []);

        $phrases = $this->invokeExtractorMethod(
            $extractor,
            'getTranslationPhrases',
            "__.p('%d test', '%d tests', count); __('Standalone');"
        );

        $this->assertSame(['Standalone'], $phrases);
    }

    /**
     * Stop singular extraction cleanly at template helper boundaries.
     *
     * @return void
     */
    public function testSingularPhraseExtractionDoesNotBleedAcrossTemplateBlocks()
    {
        $extractor = new \tao_helpers_translation_SourceCodeExtractor('', []);

        $phrases = $this->invokeExtractorMethod(
            $extractor,
            'getTranslationPhrases',
            "{{__ 'Items in use can’t be deleted'}}<br>\n"
            . "{{__ 'and'}} {{__p \"%d other.\" \"%d others.\" numberOther}}\n"
            . "{{__ 'If you continue:'}}"
        );

        $this->assertSame(
            [
                'Items in use can’t be deleted',
                'and',
                'If you continue:',
            ],
            $phrases
        );
    }

    /**
     * Extract singular and plural strings from explicit JS plural helpers.
     *
     * @return void
     */
    public function testPluralPhraseExtractionHandlesExplicitJavascriptCalls()
    {
        $extractor = new \tao_helpers_translation_SourceCodeExtractor('', []);

        $phrases = $this->invokeExtractorMethod(
            $extractor,
            'getPluralTranslationPhrases',
            "__.p('%d test', '%d tests', count);"
        );

        $this->assertSame(
            [[
                'singular' => '%d test',
                'plural' => '%d tests',
            ]],
            $phrases
        );
    }

    /**
     * Ignore malformed JS plural helpers that do not provide a count argument.
     *
     * @return void
     */
    public function testPluralPhraseExtractionSkipsTwoArgumentJavascriptTranslations()
    {
        $extractor = new \tao_helpers_translation_SourceCodeExtractor('', []);

        $phrases = $this->invokeExtractorMethod(
            $extractor,
            'getPluralTranslationPhrases',
            "__('Validation for field %s has failed', 'roles'); __p('ok', 'oks');"
        );

        $this->assertSame([], $phrases);
    }

    /**
     * Avoid treating later quoted strings as plural helper arguments.
     *
     * @return void
     */
    public function testPluralPhraseExtractionDoesNotBleedIntoLaterQuotedStrings()
    {
        $extractor = new \tao_helpers_translation_SourceCodeExtractor('', []);

        $phrases = $this->invokeExtractorMethod(
            $extractor,
            'getPluralTranslationPhrases',
            "return new common_report_Report(common_report_Report::TYPE_ERROR, __('Unable to create deployement directory'), \$xhtml);\n"
            . "\$expr = \"/http[s]?:\\/\\//mi\";"
        );

        $this->assertSame([], $phrases);
    }

    /**
     * Extract singular and plural strings from explicit template plural helpers.
     *
     * @return void
     */
    public function testPluralPhraseExtractionHandlesExplicitTemplateCalls()
    {
        $extractor = new \tao_helpers_translation_SourceCodeExtractor('', []);

        $phrases = $this->invokeExtractorMethod(
            $extractor,
            'getPluralTranslationPhrases',
            '{{__p "%d other." "%d others." numberOther}}'
        );

        $this->assertSame(
            [[
                'singular' => '%d other.',
                'plural' => '%d others.',
            ]],
            $phrases
        );
    }

    /**
     * Stop template plural extraction at the current helper block.
     *
     * @return void
     */
    public function testPluralPhraseExtractionDoesNotBleedAcrossTemplateBlocks()
    {
        $extractor = new \tao_helpers_translation_SourceCodeExtractor('', []);

        $phrases = $this->invokeExtractorMethod(
            $extractor,
            'getPluralTranslationPhrases',
            "{{__ 'and'}} {{__p \"%d other.\" \"%d others.\" numberOther}}\n"
            . "{{__ 'If you continue:'}}"
        );

        $this->assertSame(
            [[
                'singular' => '%d other.',
                'plural' => '%d others.',
            ]],
            $phrases
        );
    }

    /**
     * Ignore template helpers that are not plural calls.
     *
     * @return void
     */
    public function testPluralPhraseExtractionSkipsTwoArgumentTemplateTranslations()
    {
        $extractor = new \tao_helpers_translation_SourceCodeExtractor('', []);

        $phrases = $this->invokeExtractorMethod(
            $extractor,
            'getPluralTranslationPhrases',
            '{{__ "Desktop preview" "Mobile preview"}}'
        );

        $this->assertSame([], $phrases);
    }

    /**
     * Invoke a non-public extractor helper with test input.
     *
     * @param object $extractor The extractor instance.
     * @param string $methodName The helper method to invoke.
     * @param string $content The source content to parse.
     * @return array
     */
    private function invokeExtractorMethod($extractor, $methodName, $content)
    {
        $method = new ReflectionMethod(\tao_helpers_translation_SourceCodeExtractor::class, $methodName);
        $method->setAccessible(true);

        return $method->invoke($extractor, $content);
    }
}
