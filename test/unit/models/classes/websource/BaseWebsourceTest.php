<?php

declare(strict_types=1);

namespace oat\tao\test\model\websource;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\filesystem\FileSystem;
use oat\tao\model\websource\BaseWebsource;

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
    public function testGetMimetype($fileName, $mimeType, $expectedMimeType): void
    {
        $this->baseWebsource
            ->method('getFileSystem')
            ->willReturn($this->getFileSystemMockWithMimeType($mimeType))
        ;

        $this->assertSame($expectedMimeType, $this->baseWebsource->getMimetype($fileName));
    }

    public function mimeTypeProvider()
    {
        return [
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
            ->method('getMimetype')
            ->willReturn($mimeType);

        return $fileSystemMock;
    }
}
