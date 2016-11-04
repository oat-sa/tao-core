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

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidService;
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\metadata\exception\MetadataImportException;
use oat\tao\model\metadata\exception\injector\MetadataInjectorReadException;
use oat\tao\model\metadata\exception\injector\MetadataInjectorWriteException;
use oat\tao\model\metadata\injector\Injector;

/**
 * Class OntologyMetadataImport
 *
 * @author Camille Moyon
 * @package oat\tao\model\metadata\import
 *
 * - promote $this OntologyMetadataImport to abstract
 * - create itemMetadataImportImplementation to taoItem
 */
abstract class OntologyMetadataImporter extends ConfigurableService implements MetadataImporter
{
    use OntologyAwareTrait;

    protected $injectors = [];

    /**
     * Main method to import Iterator data to Ontology object
     *
     * @param array $data
     * @param boolean $dryrun If set to true no data will be written
     * @return \common_report_Report
     */
    public function import(array $data, $dryrun = false)
    {
        try {
            /** @var Injector[] $injectors */
            $injectors = $this->getInjectors();
        } catch (InconsistencyConfigException $e) {
            return \common_report_Report::createFailure('Config problem: ' . $e->getMessage());
        }

        // Global report
        $report = \common_report_Report::createInfo('Report of metadata import.');

        // Foreach line of dateSource
        foreach ($data as $uri => $dataSource) {
            try {

                // Check if resource exists
                $resource = $this->getResource($uri);
                if (! $resource->exists()) {
                    throw new MetadataImportException('Unable to find resource associated to uri : "' . $uri . '"');
                }

                $lineReport = \common_report_Report::createInfo('Report by line.');
                $dataSource = array_change_key_case($dataSource);

                // Foreach injector to map a target source
                /** @var Injector $injector */
                foreach ($injectors as $name => $injector) {
                    $injectorReport = null;

                    try {
                        $dataRead = $injector->read($dataSource);
                        $injector->write($resource, $dataRead, $dryrun);
                        $injectorReport = \common_report_Report::createSuccess('Injector "' . $name . '" successfully ran.');
                    } catch (MetadataInjectorReadException $e) {
                        $injectorReport = \common_report_Report::createFailure(
                            'Injector "' . $name . '" failed to run at read: ' . $e->getMessage()
                        );
                    } catch (MetadataInjectorWriteException $e) {
                        $injectorReport = \common_report_Report::createFailure(
                            'Injector "' . $name . '" failed to run at write: ' . $e->getMessage()
                        );
                    }

                    // Skip if there are no report (no data to read for this injector)
                    if (! is_null($injectorReport)) {
                        $lineReport->add($injectorReport);
                    }

                }

            } catch (MetadataImportException $e) {
                $lineReport = \common_report_Report::createFailure($e->getMessage());
            }
            $report->add($lineReport);
        }

        return $report;
    }

    /**
     * Get metadata injectors from config
     *
     * @return Injector[]
     * @throws InconsistencyConfigException
     */
    protected function getInjectors()
    {
        if (empty($this->injectors)) {
            try {
                foreach(array_keys($this->getOptions()) as $injectorName) {
                    /** @var Injector $injector */
                    $injector = $this->getSubService($injectorName, Injector::class);
                    $injector->createInjectorHelpers();
                    $this->injectors[$injectorName] = $injector;
                }
            } catch (ServiceNotFoundException $e) {
                throw new InconsistencyConfigException($e->getMessage());
            } catch (InvalidService $e) {
                throw new InconsistencyConfigException($e->getMessage());
            }

            if (empty($this->injectors)) {
                throw new InconsistencyConfigException('No injector found into config.');
            }
        }
        return $this->injectors;
    }

    public function addInjector($name, Injector $injector)
    {
        if (isset($this->injectors[$name])) {
            throw new \ConfigurationException('An injector with name "' . $name . '" already exists.');
        }

        $this->injectors[$name] = $injector;
    }

    public function __toPhpCode()
    {
        $injectorString = '';
        foreach ($this->injectors as $name => $injector) {
            $injectorString .= '    \'' . $name . '\' => ' . $injector->__toPhpCode() . PHP_EOL;
        }

        return 'new ' . get_class($this) . '(array(' . PHP_EOL . $injectorString . '))';
    }

}