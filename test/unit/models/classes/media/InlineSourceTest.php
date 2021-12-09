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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA ;
 */

declare(strict_types=1);


namespace oat\tao\test\unit\models\classes\media;

use oat\generis\test\GenerisTestCase;
use oat\tao\model\media\mediaSource\DirectorySearchQuery;
use oat\tao\model\media\sourceStrategy\InlineSource;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class InlineSourceTest extends GenerisTestCase
{
    /** @var InlineSource $sut */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new InlineSource();
    }

    public function testGetFileStream_WhenInlineAssetUrlIsCorrect_ThenStreamIsReturned()
    {
        $stream = $this->sut->getFileStream($this->inlineAssetDataUrlFixture());
        $this->assertInstanceOf(StreamInterface::class, $stream);
    }

    /**
     * @dataProvider invalidUrlProvider
     */
    public function testGetFileStream_WhenInlineAssetUrlIsInvalid_ThenExceptionThrown($url)
    {
        $this->expectException(RuntimeException::class);
        $stream = $this->sut->getFileStream($url);
    }

    public function testDownload_WhenDataUrlProvided_ThenContentsAreReturned()
    {
        $contents = $this->sut->download($this->inlineAssetDataUrlFixture());

        $this->assertEquals(base64_decode($this->base64EncodedImageFixture()), $contents);
    }

    public function testGetBaseName_WhenFileExtensionCanNotBeDetermined_ThenExceptionIsThrown()
    {
        $this->expectException(RuntimeException::class);
        $urlWithFakeMimeType = "data://text/invalid;base64,dGVzdA==";
        $this->sut->getBaseName($urlWithFakeMimeType);
    }

    public function testGetBaseName_WhenDataUrlIsProvided_ThenFilenameWithAHashIsReturned()
    {
        $name = $this->sut->getBaseName($this->inlineAssetDataUrlFixture());
        $this->assertEquals('inline-media-b9f1078dff938f6a9ffbbf12f994b577.bmp', $name);
    }

    public function testGetFileInfo_WhenInvoked_ThenExceptionThrown()
    {
        $this->expectException(\common_Exception::class);
        $this->sut->getFileInfo('');
    }

    public function testGetDirectories_WhenInvoked_ThenExceptionThrown()
    {
        $this->expectException(\common_Exception::class);
        $this->sut->getDirectories($this->createMock(DirectorySearchQuery::class));
    }

    public function testGetDirectory_WhenInvoked_ThenExceptionThrown()
    {
        $this->expectException(\common_Exception::class);
        $this->sut->getDirectory();
    }

    private function inlineAssetDataUrlFixture()
    {
        return 'data:image/bmp;base64,' . $this->base64EncodedImageFixture();
    }

    private function base64EncodedImageFixture()
    {
        return 'Qk1yAAAAAAAAAD4AAAAoAAAADgAAAA0AAAABAAEAAAAAADQAAADEDgAAxA4AAAIAAAACAAAA////AAAAAAAAAAAAAAAOAAAApgAHwLQAHGD0ADAw5gBgGLYAAgAAAAAAAAAQIAAAAAAAAAACAAAAAwq3';
    }

    public function invalidUrlProvider(): array
    {
        return [
            'Invalid mime type' => [
                "data://invalid;base64,"
            ],
            'Invalid encoding' => [
                "data://text/plain;invalid,"
            ],
            'Invalid scheme' => [
                "invalid://text/plain;base64,dGVzdA=="
            ],
        ];
    }
}
