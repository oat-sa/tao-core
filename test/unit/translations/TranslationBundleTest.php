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

            $content = json_decode(file_get_contents($file), true);
            $this->assertTrue(is_array($content));
            $this->assertEquals($expectedSerial, $content['serial']);
        }
    }
}
