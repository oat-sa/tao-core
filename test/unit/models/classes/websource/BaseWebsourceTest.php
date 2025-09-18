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
 * Copyright (c) 2018-2025 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\model\websource;

use PHPUnit\Framework\TestCase;
use oat\oatbox\filesystem\FileSystem;
use oat\tao\model\websource\BaseWebsource;
use PHPUnit\Framework\MockObject\MockObject;

class BaseWebsourceTest extends TestCase
{
    /** @var BaseWebsource | MockObject */
    private $baseWebsource;

    protected function setUp(): void
    {
        $this->baseWebsource = $this->getMockForAbstractClass(
            BaseWebsource::class,
            [],
            '',
            true,
            true,
            true,
            ['getFileSystem']
        );
    }

    /**
     * @dataProvider mimeTypeProvider
     */
    public function testGetMimetype($fileName, $mimeType, $expectedMimeType)
    {
        $this->baseWebsource
            ->method('getFileSystem')
            ->willReturn($this->getFileSystemMockWithMimeType($mimeType))
        ;

        $this->assertEquals($expectedMimeType, $this->baseWebsource->getMimetype($fileName));
    }

    public function mimeTypeProvider()
    {
        return [
            ['test.js', 'text/tex', 'text/javascript'],
            ['test.js', 'text/plain', 'text/javascript'],
            ['test.js', 'text/x-asm', 'text/javascript'],
            ['test.js', 'text/x-c', 'text/javascript'],
            ['test.css', 'text/plain', 'text/css'],
            ['test.css', 'text/x-asm', 'text/css'],
            ['test.svg', 'text/plain', 'image/svg+xml'],
            ['test.mp3', 'test/test', 'audio/mpeg'],
            ['test.test', 'test/test', 'test/test'],
        ];
    }

    private function getFileSystemMockWithMimeType($mimeType)
    {
        $fileSystemMock = $this
            ->getMockBuilder(FileSystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileSystemMock
            ->method('mimeType')
            ->willReturn($mimeType);

        return $fileSystemMock;
    }
}
