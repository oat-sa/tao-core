<?php

namespace oat\tao\test\model\websource;

use oat\generis\test\TestCase;
use oat\oatbox\filesystem\FileSystem;
use oat\tao\model\websource\BaseWebsource;

class BaseWebsourceTest extends TestCase
{
    /** @var BaseWebsource | \PHPUnit_Framework_MockObject_MockObject */
    private $baseWebsource;

    public function setUp()
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