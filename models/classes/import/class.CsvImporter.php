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
 * Copyright (c) 2013-2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

use oat\generis\model\OntologyRdfs;
use oat\oatbox\event\EventManagerAwareTrait;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\event\CsvImportEvent;
use oat\tao\model\import\ImportHandlerHelperTrait;
use oat\tao\model\import\TaskParameterProviderInterface;
use oat\tao\model\upload\UploadService;
use oat\tao\model\import\CsvAbstractImporter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Basic import of csv files
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class tao_models_classes_import_CsvImporter extends CsvAbstractImporter implements tao_models_classes_import_ImportHandler, ServiceLocatorAwareInterface, TaskParameterProviderInterface
{
    use EventManagerAwareTrait;
    use ImportHandlerHelperTrait {
        getTaskParameters as getDefaultTaskParameters;
    }

    const OPTION_POSTFIX = '_O';

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_import_ImportHandler::getLabel()
     */
    public function getLabel()
    {
        return __('CSV');
    }

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_import_ImportHandler::getForm()
     */
    public function getForm()
    {
        $form = empty($_POST['source']) && empty($_POST['importFile'])
            ? new tao_models_classes_import_CsvUploadForm()
            : $this->createImportFormContainer();
        return $form->getForm();
    }

    /**
     * Constructs the Import form container
     * In need of a major refactoring, which will
     * probably involve refactoring the Form engine as well
     *
     * @return tao_models_classes_import_CSVMappingForm
     * @throws \oat\generis\model\fileReference\FileSerializerException
     * @throws common_exception_NotAcceptable
     */
    private function createImportFormContainer()
    {
        $sourceContainer = new tao_models_classes_import_CsvUploadForm();
        $sourceForm = $sourceContainer->getForm();
        /** @var tao_helpers_form_FormElement $element */
        foreach ($sourceForm->getElements() as $element) {
            $element->feed();
        }

        $sourceForm->getElement('source')->feed();
        $fileInfo = $sourceForm->getValue('source');

        if (isset($_POST['importFile'])) {
            $serial = $_POST['importFile'];
        } else {
            $serial = $fileInfo['uploaded_file'];
        }

        if (!is_string($serial)) {
            throw new InvalidArgumentException('Import file has to be a valid file serial.');
        }

        /** @var UploadService $uploadService */
        $uploadService = $this->getServiceManager()->get(UploadService::SERVICE_ID);
        $file = $uploadService->getUploadedFlyFile($serial);

        $properties = [tao_helpers_Uri::encode(OntologyRdfs::RDFS_LABEL) => __('Label')];
        $rangedProperties = [];

        $classUri = \tao_helpers_Uri::decode($_POST['classUri']);
        $class = new core_kernel_classes_Class($classUri);
        $classProperties = $this->getClassProperties($class);

        foreach ($classProperties as $property) {
            if (!in_array($property->getUri(), $this->getExludedProperties())) {
                //@todo manage the properties with range
                $range = $property->getRange();
                $properties[tao_helpers_Uri::encode($property->getUri())] = $property->getLabel();

                if ($range instanceof core_kernel_classes_Resource && $range->getUri() != OntologyRdfs::RDFS_LITERAL) {
                    $rangedProperties[tao_helpers_Uri::encode($property->getUri())] = $property->getLabel();
                }
            }
        }

        //load the csv data from the file (uploaded in the upload form) to get the columns
        $csv_data = new tao_helpers_data_CsvFile($sourceForm->getValues());
        $csv_data->load($file);

        $values = $sourceForm->getValues();
        $values[tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES] = !empty($values[tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES]);
        $values['importFile'] = $serial;
        $myFormContainer = new tao_models_classes_import_CSVMappingForm($values, [
            'class_properties' => $properties,
            'ranged_properties' => $rangedProperties,
            'csv_column' => $this->getColumnMapping($csv_data, $sourceForm->getValue(tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES)),
            tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES => $sourceForm->getValue(tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES),
        ]);

        return $myFormContainer;
    }

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_import_ImportHandler::import()
     * @param core_kernel_classes_Class $class
     * @param tao_helpers_form_Form|array $form
     * @param string|null $userId owner of the resource
     * @return common_report_Report
     * @throws \oat\oatbox\service\ServiceNotFoundException
     * @throws \common_Exception
     */
    public function import($class, $form, $userId = null)
    {
        // for backward compatibility
        $options = $form instanceof \tao_helpers_form_Form ? $form->getValues() : $form;

        $options['file'] = $this->fetchUploadedFile($form);

        // Clean "csv_select" values from form view.
        // Transform any "csv_select" in "csv_null" in order to
        // have the same importation behaviour for both because
        // semantics are the same.

        // for backward compatibility
        $map = $form instanceof \tao_helpers_form_Form ? $form->getValues('property_mapping') : $form['property_mapping'];
        $newMap = [];

        foreach ($map as $k => $m) {
            if ($m !== 'csv_select') {
                $newMap[$k] = $map[$k];
            } else {
                $newMap[$k] = 'csv_null';
            }
            $newMap[$k] = str_replace(self::OPTION_POSTFIX, '', $newMap[$k]);
            common_Logger::d('map: ' . $k . ' => ' . $newMap[$k]);
        }
        $options['map'] = $newMap;

        $staticMap = [];

        // for backward compatibility
        $rangedProperties = $form instanceof \tao_helpers_form_Form ? $form->getValues('ranged_property') : $form['ranged_property'];

        foreach ($rangedProperties as $propUri => $value) {
            if (strpos($propUri, tao_models_classes_import_CSVMappingForm::DEFAULT_VALUES_SUFFIX) !== false) {
                $cleanUri = str_replace(tao_models_classes_import_CSVMappingForm::DEFAULT_VALUES_SUFFIX, '', $propUri);
                $staticMap[$cleanUri] = $value;
            }
        }
        $options['staticMap'] = array_merge($staticMap, $this->getStaticData());

        $report = parent::importFile($class, $options, $userId);

        if ($report->getType() == common_report_Report::TYPE_SUCCESS) {
            $this->getEventManager()->trigger(new CsvImportEvent($report));
        }

        return $report;
    }

    /**
     * Defines the task parameters to be stored for later use.
     *
     * @param tao_helpers_form_Form $form
     * @return array
     */
    public function getTaskParameters(tao_helpers_form_Form $form)
    {
        return array_merge(
            $form->getValues(),
            [
                'property_mapping' => $form->getValues('property_mapping'),
                'ranged_property' => $form->getValues('ranged_property')
            ],
            $this->getDefaultTaskParameters($form)
        );
    }

    /**
     * @return ServiceManager
     */
    private function getServiceManager()
    {
        return ServiceManager::getServiceManager();
    }
}
