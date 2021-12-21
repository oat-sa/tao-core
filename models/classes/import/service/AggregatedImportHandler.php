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
use oat\tao\model\upload\UploadService;
use oat\taoQtiItem\model\import\CsvImportForm;
use tao_helpers_form_Form;
use oat\tao\model\import\TaskParameterProviderInterface;
use tao_models_classes_import_ImportHandler;
use Throwable;

class AggregatedImportHandler implements tao_models_classes_import_ImportHandler, TaskParameterProviderInterface
{
    /** @var UploadService */
    private $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return __('CSV metadata');
    }

    /**
     * @inheritdoc
     */
    public function getForm()
    {
        $form = new CsvImportForm(
            [],
            [
                'classUri' => null
            ]
        );

        return $form->getForm();
    }

    /**
     * @inheritdoc
     */
    public function import($class, $form, $userId = null)
    {
        try {
            $uploadedFile = $this->uploadService->fetchUploadedFile($form);

            $this->processFile($uploadedFile);
        } catch (Throwable $exception) {
            return Report::create(
                Report::TYPE_ERROR,
                $exception->getMessage()
            );
        } finally {
            $this->uploadService->remove($uploadedFile);
        }

        return Report::create(
            Report::TYPE_SUCCESS,
            'Import with success'
        );
    }

    private function processFile(File $file): void
    {
        // @TODO Upload processing here - Will be done in future task.
    }

    /**
     * @inheritDoc
     */
    public function getTaskParameters(tao_helpers_form_Form $importForm)
    {
        $file = $this->uploadService->getUploadedFlyFile(
            $importForm->getValue('importFile') ?: $importForm->getValue('source')['uploaded_file']
        );

        return [
            'uploaded_file' => $file->getPrefix(),
        ];
    }
}
