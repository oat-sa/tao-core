<?php

/*
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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\tao\model\import\service;

use oat\oatbox\filesystem\File;
use oat\oatbox\reporting\Report;
use oat\tao\model\import\Processor\ImportFileErrorHandlerInterface;
use oat\tao\model\import\Processor\ImportFileProcessorInterface;
use oat\tao\model\upload\UploadService;
use tao_helpers_form_Form;
use oat\tao\model\import\TaskParameterProviderInterface;
use tao_models_classes_import_CsvUploadForm;
use tao_models_classes_import_ImportHandler;
use Throwable;

class AgnosticImportHandler implements tao_models_classes_import_ImportHandler, TaskParameterProviderInterface
{
    /** @var UploadService */
    private $uploadService;

    /** @var string|null */
    private $label;

    /** @var tao_helpers_form_Form|null */
    private $form;

    /** @var ImportFileProcessorInterface|null */
    private $fileProcessor;

    /** @var ImportFileErrorHandlerInterface|null */
    private $errorHandler;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function withFileProcessor(ImportFileProcessorInterface $fileProcessor): self
    {
        $this->fileProcessor = $fileProcessor;

        return $this;
    }

    public function withErrorHandler(ImportFileErrorHandlerInterface $errorHandler): self
    {
        $this->errorHandler = $errorHandler;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label ?? 'Import';
    }

    /**
     * @inheritdoc
     */
    public function getForm()
    {
        if ($this->form) {
            return $this->form;
        }

        return (new tao_models_classes_import_CsvUploadForm())->getForm();
    }

    public function withLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function withForm(tao_helpers_form_Form $form): self
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function import($class, $form, $userId = null)
    {
        try {
            $uploadedFile = $this->uploadService->fetchUploadedFile($form);

            return $this->processFile($uploadedFile);
        } catch (Throwable $exception) {
            return $this->handleException($exception);
        } finally {
            $this->uploadService->remove($uploadedFile);
        }
    }

    private function processFile(File $file): Report
    {
        if ($this->fileProcessor) {
            return $this->fileProcessor->process($file);
        }

        $subReport = Report::create(
            Report::TYPE_SUCCESS,
            'Imported %s with success',
            [
                $file->getBasename()
            ]
        )->setData([]);

        $report = Report::createSuccess(__('Imported successfully finished!'))
            ->setData([])
            ->add($subReport);

        return $report;
    }

    private function handleException(Throwable $exception): Report
    {
        if ($this->errorHandler) {
            return $this->errorHandler->handle($exception);
        }

        return Report::create(
            Report::TYPE_ERROR,
            $exception->getMessage()
        );
    }

    /**
     * @inheritDoc
     */
    public function getTaskParameters(tao_helpers_form_Form $importForm): array
    {
        $file = $this->uploadService->getUploadedFlyFile($this->getUploadedFile($importForm));

        return [
            'uploaded_file' => $file->getPrefix(),
        ];
    }

    private function getUploadedFile(tao_helpers_form_Form $importForm): string
    {
        return $importForm->getValue('importFile') ?: $importForm->getValue('source')['uploaded_file'];
    }
}
