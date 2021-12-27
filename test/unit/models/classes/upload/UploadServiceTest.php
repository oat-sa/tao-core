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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\upload;

use common_exception_Error;
use oat\generis\test\TestCase;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use PHPUnit\Framework\MockObject\MockObject;
use tao_helpers_form_Form;

class UploadServiceTest extends TestCase
{
    /** @var UploadService */
    private $subject;

    /** @var FileSystemService|MockObject */
    private $fileSystemService;

    /** @var Directory|MockObject */
    private $directory;

    public function setUp(): void
    {
        $this->fileSystemService = $this->createMock(FileSystemService::class);
        $this->directory = $this->createMock(Directory::class);
        $this->subject = new UploadService();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    FileSystemService::SERVICE_ID => $this->fileSystemService,
                ]
            )
        );
    }

    public function testFetchUploadedFileWithFilePath(): void
    {
        $form = $this->createMock(tao_helpers_form_Form::class);
        $form->method('getValue')
            ->willReturn('filePath');

        $this->assertSame('filePath', $this->subject->fetchUploadedFile($form));
    }

    public function testFetchUploadedFileWithFileObject(): void
    {
        $file = $this->createMock(File::class);

        $this->fileSystemService
            ->method('getDirectory')
            ->willReturn($this->directory);

        $this->directory
            ->method('getFile')
            ->willReturn($file);

        $this->assertSame(
            $file,
            $this->subject->fetchUploadedFile(['uploaded_file' => 'something'])
        );
    }

    public function testFetchUploadedFileWithInvalidArgumentWillThrowException(): void
    {
        $this->expectException(common_exception_Error::class);
        $this->subject->fetchUploadedFile('unsupported');
    }
}

