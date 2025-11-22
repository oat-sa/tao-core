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
 * Copyright (c) 2020-2023 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\scripts\tools\migrations;

use Doctrine\Migrations\Version\Version;
use common_ext_ExtensionsManager as ExtensionsManager;
use common_ext_Extension as Extension;
use PHPUnit\Framework\TestCase;
use oat\tao\scripts\tools\migrations\TaoComparator;
use helpers_ExtensionHelper as ExtensionHelper;

/**
 * Class TaoComparator
 * @package oat\tao\scripts\tools\migrations
 */
class TaoComparatorTest extends TestCase
{
    public function testCompare()
    {
        $extensionsManagerMock = $this->getExtensionManagerMock();
        $comparator = new TaoComparator($extensionsManagerMock, $this->getExtensionHelperMock());
        $versions = [
            $versionFoo5 = new Version('Version5_foo'),
            $versionFoo3 = new Version('Version3_foo'),
            $extensionBar4 = new Version('Version4_bar'),
            $extensionBar2 = new Version('Version2_bar'),
            $extensionBaz1 = new Version('Version1_baz'),
            $camelCaseVersion = new Version('Version1Baz'),
            $smallLetterExtension = new Version('Version1baz'),
        ];

        usort($versions, [$comparator, 'compare']);
        $versionKeys = [];
        foreach ($versions as $version) {
            $versionKeys[] = (string) $version;
        }
        $this->assertEquals([
            'Version3_foo',
            'Version5_foo',
            'Version1_baz',
            'Version1Baz',
            'Version1baz',
            'Version2_bar',
            'Version4_bar',
        ], $versionKeys);

        $this->assertTrue(0 > $comparator->compare(new Version('Version5_foo'), new Version('Version4_bar')));
        $this->assertTrue(0 < $comparator->compare(new Version('Version4_bar'), new Version('Version1_baz')));
    }

    private function getExtensionManagerMock()
    {
        $extensionFoo = $this->getMockBuilder(Extension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionFoo->method('getId')
            ->willReturn('foo');
        $extensionFoo->method('getDependencies')
            ->willReturn([]);

        $extensionBar = $this->getMockBuilder(Extension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionBar->method('getId')
            ->willReturn('bar');
        $extensionBar->method('getDependencies')
            ->willReturn(['foo' => '*', 'baz' => '*']);

        $extensionBaz = $this->getMockBuilder(Extension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionBaz->method('getId')
            ->willReturn('baz');
        $extensionBaz->method('getDependencies')
            ->willReturn(['foo' => '*']);


        $extensionsManagerMock = $this->getMockBuilder(ExtensionsManager::class)
            ->getMock();
        $extensionsManagerMock->method('getInstalledExtensions')
            ->willReturn([
                'bar' => $extensionBar,
                'foo' => $extensionFoo
            ]);
        $extensionsManagerMock->method('getExtensionById')
            ->willReturnMap([
                ['baz', $extensionBaz],
                ['bar', $extensionBar],
                ['foo', $extensionFoo]
            ]);
        return $extensionsManagerMock;
    }

    private function getExtensionHelperMock()
    {
        return new ExtensionHelper();
    }
}
