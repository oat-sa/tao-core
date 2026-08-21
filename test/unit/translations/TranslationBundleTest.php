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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @license GPLv2
 * @package tao
 *
 */

namespace oat\tao\test\unit\translation;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use oat\tao\helpers\translation\TranslationBundle;
use tao_helpers_File;

/**
 * Unit and Integration(fs) test {@link oat\tao\heplers\translation\TranslationBundle}
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @package tao
 */
class TranslationBundleTest extends TestCase
{
    /**
     * A temporary directory to write translations bundles
     */
    private static $tmpDir;

    /**
     * Set up the temp directory
     */
    public static function setUpBeforeClass(): void
    {
        self::$tmpDir = \tao_helpers_File::createTempDir();
    }

    /**
     * Removes the temporary directory
     */
    public static function tearDownAfterClass(): void
    {
        tao_helpers_File::delTree(self::$tmpDir);
    }

    /**
     * Provides wrong constructor parameters
     * @return array() the data
     */
    public function wrongConstructorProvider()
    {
        return [
            [true, [], null],
            ['test', 12, 10],
            [null, null, false],
        ];
    }

    /**
     * Test constructor with wrong parameters
     * @param string $langCode
     * @param array $extensions
     * @dataProvider wrongConstructorProvider
     */
    public function testWrongConstructor($langCode, $extensions, $basePath)
    {
        $this->expectException(InvalidArgumentException::class);
        new TranslationBundle($langCode, $extensions, $basePath);
    }

    /**
     * Provides data to test the bundle
     * @return array() the data
     */
    public function bundleProvider()
    {
        return [
           ['en-US', ['tao', 'taoItems'], md5('en-US_tao-taoItems')],
           ['fr-FR', ['tao', 'taoItems'], md5('fr-FR_tao-taoItems')],
        ];
    }

    /**
     * Test the bundle
     * @param string $langCode
     * @param array $extensions
     * @dataProvider bundleProvider
     */
    public function testBundle($langCode, $extensions, $expectedSerial)
    {
        $bundle = new TranslationBundle($langCode, $extensions, __DIR__ . '/../../../');

        $serial = $bundle->getSerial();
        $this->assertTrue(is_string($serial));
        $this->assertEquals($expectedSerial, $serial);

        if (is_dir(self::$tmpDir)) {
            $file = $bundle->generateTo(self::$tmpDir);
            $this->assertTrue(file_exists($file));
            $this->assertTrue(file_exists(dirname($file) . '/messages.js'));

            $content = json_decode(file_get_contents($file), true);
            $this->assertTrue(is_array($content));
            $this->assertEquals($expectedSerial, $content['serial']);
        }
    }

    /**
     * Keep plural metadata when bundling structured messages.
     *
     * @return void
     */
    public function testBundleKeepsPluralFormsFromStructuredMessages()
    {
        $basePath = self::$tmpDir . '/bundle-fixtures-' . uniqid('', true);
        $extensionPath = $basePath . '/fakeExt/locales/sk-SK';

        mkdir($extensionPath, 0777, true);
        file_put_contents(
            $extensionPath . '/messages_po.js',
            json_encode([
                'pluralForms' => 'nplurals=4; plural=(n==1) ? 0 : (n>=2 && n<=4) ? 1 : 3;',
                'translations' => [
                    '%d day' => [
                        '_plural' => '%d days',
                        '_translations' => ['%d den', '%d dni', '%d dni', '%d dni'],
                    ],
                    'mock-1' => 'translation mock 1',
                ],
            ])
        );

        $bundle = new TranslationBundle('sk-SK', ['fakeExt'], $basePath);
        $file = $bundle->generateTo(self::$tmpDir);
        $content = json_decode(file_get_contents($file), true);
        $module = file_get_contents(dirname($file) . '/messages.js');

        $this->assertSame(
            'nplurals=4; plural=(n==1) ? 0 : (n>=2 && n<=4) ? 1 : 3;',
            $content['pluralForms']
        );
        $this->assertSame('%d days', $content['translations']['%d day']['_plural']);
        $this->assertStringContainsString('p11nRules: function (n)', $module);
        $this->assertStringContainsString('Number((n==1) ? 0 : (n>=2 && n<=4) ? 1 : 3)', $module);

        tao_helpers_File::delTree($basePath);
    }

    /**
     * Support the legacy flat `messages_po.js` format.
     *
     * @return void
     */
    public function testBundleSupportsLegacyFlatMessagesFormat()
    {
        $basePath = self::$tmpDir . '/bundle-legacy-' . uniqid('', true);
        $extensionPath = $basePath . '/legacyExt/locales/en-US';

        mkdir($extensionPath, 0777, true);
        file_put_contents(
            $extensionPath . '/messages_po.js',
            json_encode([
                'mock-1' => 'translation mock 1',
            ])
        );

        $bundle = new TranslationBundle('en-US', ['legacyExt'], $basePath);
        $file = $bundle->generateTo(self::$tmpDir);
        $content = json_decode(file_get_contents($file), true);

        $this->assertSame('translation mock 1', $content['translations']['mock-1']);
        $this->assertArrayNotHasKey('pluralForms', $content);

        tao_helpers_File::delTree($basePath);
    }
}
