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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\import;

use oat\oatbox\filesystem\File;
use oat\tao\model\upload\UploadService;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

trait ImportHandlerHelperTrait
{
    use ServiceLocatorAwareTrait;

    /**
     * Helps to get the uploaded file data during upload or during processing of the import task.
     *
     * @param array|\tao_helpers_form_Form $form
     * @return File|string
     * @throws \common_exception_Error
     */
    protected function fetchUploadedFile($form)
    {
        $file = '';

        // for backward compatibility
        if ($form instanceof \tao_helpers_form_Form) {
            $fileInfo = $form->getValue('source');

            /** @var string $file */
            $file = $form->getValue('importFile') ?: $fileInfo['uploaded_file']; // key "importFile" used in CSV import
        } else if (isset($form['uploaded_file'])) {
            /** @var File $file */
            $file = $this->getUploadService()->getUploadDir()->getFile($form['uploaded_file']);
        }

        if (!$file) {
            throw new \common_exception_Error('No source file for import');
        }

        return $file;
    }

    /**
     * @return UploadService|object
     */
    protected function getUploadService()
    {
        return $this->getServiceLocator()->get(UploadService::SERVICE_ID);
    }

    public function getTaskParameters(\tao_helpers_form_Form $importForm)
    {
        // key "importFile" used in CSV import
        $file = $this->getUploadService()->getUploadedFlyFile($importForm->getValue('importFile') ?: $importForm->getValue('source')['uploaded_file']);

        return [
            'uploaded_file' => $file->getPrefix(), // because of Async, we need the full path of the uploaded file
        ];
    }
}