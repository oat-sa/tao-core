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

namespace oat\tao\test\unit\import\service;

use core_kernel_classes_Class;
use Exception;
use oat\oatbox\filesystem\File;
use oat\oatbox\reporting\Report;
use oat\tao\model\import\Processor\ImportFileErrorHandlerInterface;
use oat\tao\model\import\Processor\ImportFileProcessorInterface;
use oat\tao\model\import\service\AgnosticImportHandler;
use PHPUnit\Framework\TestCase;
use oat\tao\model\upload\UploadService;
use PHPUnit\Framework\MockObject\MockObject;
use tao_helpers_form_Form;

class AgnosticImportHandlerTest extends TestCase
{
    /** @var AgnosticImportHandler */
    private $subject;

    /** @var UploadService|MockObject */
    private $uploadService;

    /** @var ImportFileErrorHandlerInterface|MockObject */
    private $errorHandler;

    /** @var ImportFileProcessorInterface|MockObject */
    private $processor;

    protected function setUp(): void
    {
        $this->errorHandler = $this->createMock(ImportFileErrorHandlerInterface::class);
        $this->processor = $this->createMock(ImportFileProcessorInterface::class);
        $this->uploadService = $this->createMock(UploadService::class);
        $this->subject = (new AgnosticImportHandler($this->uploadService))
            ->withErrorHandler($this->errorHandler)
            ->withFileProcessor($this->processor);
    }

    public function testGetters(): void
    {
        $form = $this->createMock(tao_helpers_form_Form::class);

        $this->subject
            ->withForm($form)
            ->withLabel('label');

        $this->assertSame($form, $this->subject->getForm());
        $this->assertSame('label', $this->subject->getLabel());
    }

    public function testImport(): void
    {
        $report = $this->createMock(Report::class);
        $class = $this->createMock(core_kernel_classes_Class::class);
        $form = $this->createMock(tao_helpers_form_Form::class);
        $file = $this->createMock(File::class);

        $this->uploadService
            ->expects($this->once())
            ->method('fetchUploadedFile')
            ->willReturn($file);

        $this->uploadService
            ->expects($this->once())
            ->method('remove')
            ->with($file);

        $this->processor
            ->expects($this->once())
            ->method('process')
            ->with($file)
            ->willReturn($report);

        $this->assertSame($report, $this->subject->import($class, $form));
    }

    public function testImportFail(): void
    {
        $report = $this->createMock(Report::class);
        $class = $this->createMock(core_kernel_classes_Class::class);
        $form = $this->createMock(tao_helpers_form_Form::class);
        $file = $this->createMock(File::class);
        $exception = new Exception('Does not matter');

        $this->uploadService
            ->expects($this->once())
            ->method('fetchUploadedFile')
            ->willReturn($file);

        $this->uploadService
            ->expects($this->once())
            ->method('remove')
            ->with($file);

        $this->processor
            ->expects($this->once())
            ->method('process')
            ->with($file)
            ->willThrowException($exception);

        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($exception)
            ->willReturn($report);

        $this->assertSame($report, $this->subject->import($class, $form));
    }
}
