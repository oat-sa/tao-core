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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

use oat\generis\Helper\SystemHelper;

/**
 * Export form for QTI packages
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
class tao_models_classes_import_CsvUploadForm extends tao_helpers_form_FormContainer
{
    public const IS_OPTION_FIRST_COLUMN_ENABLE = 'enable_option_first_columns';

    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---
    /**
     * Short description of method initForm
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function initForm()
    {
        $this->form = new tao_helpers_form_xhtml_Form('export');
        $submitElt = tao_helpers_form_FormFactory::getElement('import', 'Free');
        $submitElt->setValue(
            "<input type='button' class='btn-success small form-refresher' value='"
              . __('Next') . "' />"
        );

        $this->form->setActions([$submitElt], 'bottom');
        $this->form->setActions([], 'top');
    }

    /**
     * overriden
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function initElements()
    {
        //create file upload form box
        $fileElt = tao_helpers_form_FormFactory::getElement('source', 'AsyncFile');
        $fileElt->setDescription(__("Add a CSV file"));
        if (isset($_POST['import_sent_csv'])) {
            $fileElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
        } else {
            $fileElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty', ['message' => '']));
        }
        $fileElt->addValidators([
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
                        'application/excel',
                        'application/vnd.ms-excel',
                        'application/vnd.msexcel',
                    ],
                    'extension' => ['csv', 'txt']
                ]
            ),
            tao_helpers_form_FormFactory::getValidator(
                'FileSize',
                ['max' => SystemHelper::getFileUploadLimit()]
            )
        ]);

        $this->form->addElement($fileElt);
        $this->form->createGroup('file', __('Import Metadata from CSV file'), ['source']);

        $csvSentElt = tao_helpers_form_FormFactory::getElement('import_sent_csv', 'Hidden');
        $csvSentElt->setValue(1);
        $this->form->addElement($csvSentElt);

        // options
        $optDelimiter = tao_helpers_form_FormFactory::getElement(tao_helpers_data_CsvFile::FIELD_DELIMITER, 'Textbox');
        $optDelimiter->setDescription(__("Field delimiter"));
        $optDelimiter->setValue(';');
        $optDelimiter->addAttribute("size", 6);
        $optDelimiter->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
        $this->form->addElement($optDelimiter);

        $optEncloser = tao_helpers_form_FormFactory::getElement(tao_helpers_data_CsvFile::FIELD_ENCLOSER, 'Textbox');
        $optEncloser->setDescription(__("Field encloser"));
        $optEncloser->setValue('"');
        $optEncloser->addAttribute("size", 6);
        $optEncloser->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
        $this->form->addElement($optEncloser);

        if (isset($this->options[static::IS_OPTION_FIRST_COLUMN_ENABLE])) {
            if ($this->options[static::IS_OPTION_FIRST_COLUMN_ENABLE] === true) {
                $optFirstColumn = $this->addFirstColumnElement();
            }
        } else {
            //backwards compatible
            $optFirstColumn = $this->addFirstColumnElement();
        }

        $opts = [$optDelimiter, $optEncloser];

        if (isset($optFirstColumn)) {
            $opts[] = $optFirstColumn;
        }

        $this->form->createGroup('options', __('CSV Options'), $opts);
    }

    /**
     * @return tao_helpers_form_FormElement
     * @throws Exception
     * @throws common_Exception
     */
    protected function addFirstColumnElement()
    {
        $optFirstColumn = tao_helpers_form_FormFactory::getElement(
            tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES,
            'Checkbox'
        );
        $optFirstColumn->setDescription(__("First row column names"));
        $optFirstColumn->setOptions([tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES => '']);
        $optFirstColumn->setValue(tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES);
        $this->form->addElement($optFirstColumn);

        return $optFirstColumn;
    }
}
