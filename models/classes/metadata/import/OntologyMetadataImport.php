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
 * Copyright (c) 2016 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\metadata\import;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\metadata\injector\Injector;
use oat\tao\model\metadata\MetadataFactory;

/**
 * Class OntologyMetadataImport
 * @package oat\tao\model\metadata\import
 *
 * @todo Move item to taoItems, if not possible to have default ontology object:
 * - promote $this OntologyMetadataImport to abstract
 * - create itemMetadataImportImplementation to taoItem
 */
class OntologyMetadataImport extends ConfigurableService implements MetadataImporter
{
    /**
     * Factory to build metadata object (injector, reader, writer)
     *
     * @var MetadataFactory
     */
    protected $factory;

    /**
     * Main method to import Iterator data to Ontology object
     *
     * @todo Catch more specific exceptions
     *
     * @param \Iterator $dataIterator
     * @return \common_report_Report
     */
    public function import(\Iterator $dataIterator)
    {
        // Workaround to have array $key => $value where $key is the header col
        $dataIterator->rewind();
        $headers = $dataIterator->current();
        $dataIterator->setHeaders($headers);
        $dataIterator->next();

        try {
            /** @var Injector[] $injectors */
            $injectors = $this->getMetadataFactory()->createInjectors($this->getOptions());
        } catch (InconsistencyConfigException $e) {
            return \common_report_Report::createFailure('Config problem :' . $e->getMessage());
        }

        $report = \common_report_Report::createInfo('Report of metadata import.');

        // Foreach line of dateSource iterator
        foreach ($dataIterator as $keySource => $dataSource) {

            $data = [];
            $error = false;
            $report = \common_report_Report::createInfo('Report of metadata import.');

            // Report to handle injector error
            $injectorReport = \common_report_Report::createFailure('Error on line: ' . $keySource);

            // Foreach injector to map a target source
            foreach ($injectors as $injector) {
                try {
                    $data = array_merge($data, $injector->readValues($dataSource));
                } catch (\Exception $e) {
                    $error = true;
                    $injectorReport->add(\common_report_Report::createFailure($e->getMessage()));
                }
            }

            // If no error, resource is created and populated with writers
            if ($error === false) {
                $resource = $this->createResource();
                // Foreach injector write value to resource with mapped source
                foreach ($injectors as $injector) {
                    $injector->writeValues($resource, $data);
                }
                $reportByLine = \common_report_Report::createSuccess('Line ' . $keySource . ' successfully imported.');
            } else {
                // If error, return injector report as trace
                $reportByLine = $injectorReport;
            }

            $report->add($reportByLine);
        }

        return $report;
    }

    /**
     * Create a resource to store readers values by writers
     *
     * @return \core_kernel_classes_Resource
     */
    protected function createResource()
    {
        $class = \taoItems_models_classes_ItemsService::singleton()->getRootClass();
        return \taoItems_models_classes_ItemsService::singleton()->createInstance($class);
    }

    /**
     * Return factory to create metadata stuff
     *
     * @return MetadataFactory
     */
    protected function getMetadataFactory()
    {
        if (! $this->factory) {
            $this->factory = new MetadataFactory();
        }
        return $this->factory;
    }

}