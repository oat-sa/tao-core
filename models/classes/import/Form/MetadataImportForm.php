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

namespace oat\tao\model\import\Form;

use oat\generis\Helper\SystemHelper;
use tao_helpers_form_FormElement;
use tao_helpers_form_FormFactory;
use tao_helpers_form_xhtml_Form;
use tao_models_classes_import_CsvUploadForm;

class MetadataImportForm extends tao_models_classes_import_CsvUploadForm
{
    /** @var array */
    private $requestData = [];

    public function __construct(array $data = [], array $options = [])
    {
        $options[tao_models_classes_import_CsvUploadForm::IS_OPTION_FIRST_COLUMN_ENABLE] = false;

        parent::__construct($data, $options);
    }

    public function withRequestData(array $requestData): self
    {
        $this->requestData = $requestData;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function initForm()
    {
        $this->form = new tao_helpers_form_xhtml_Form('export');
        $submitElt = tao_helpers_form_FormFactory::getElement('import', 'Free');
        $submitElt->setValue(
            '<a href="#" class="form-submitter btn-success small"><span class="icon-import"></span> ' .
            __('Import') .
            '</a>'
        );

        $this->form->setActions([$submitElt], 'bottom');
        $this->form->setActions([], 'top');
    }

    /**
     * @inheritdoc
     */
    public function initElements()
    {
        $this->form->addElement($this->getFileUploadElement());

        $csvSentElt = tao_helpers_form_FormFactory::getElement('import_sent_csv', 'Hidden');
        $csvSentElt->setValue(1);

        $this->form->addElement($csvSentElt);
    }

    private function getFileUploadElement(): tao_helpers_form_FormElement
    {
        $element = tao_helpers_form_FormFactory::getElement('source', 'AsyncFile');
        $element->addValidators(
            [
                tao_helpers_form_FormFactory::getValidator(
                    'FileMimeType',
                    [
                        'mimetype' => [
                            'text/plain',
                            'text/csv',
                            'text/comma-separated-values',
                            'text/anytext',
                            'application/csv',
                            'application/txt',
                            'application/csv-tab-delimited-table',
                            'application/vnd.ms-excel',
                            'application/vnd.msexcel',
                        ],
                        'extension' => ['csv', 'txt']
                    ]
                ),
                tao_helpers_form_FormFactory::getValidator(
                    'FileSize',
                    [
                        'max' => SystemHelper::getFileUploadLimit()
                    ]
                )
            ]
        );

        if (isset($this->requestData['import_sent_csv'])) {
            $element->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));

            return $element;
        }

        $element->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty', ['message' => '']));

        return $element;
    }
}
