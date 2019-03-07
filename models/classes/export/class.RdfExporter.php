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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

use oat\oatbox\event\EventManagerAwareTrait;
use oat\tao\model\event\RdfExportEvent;

/**
 * The tao default rdf export
 *
 * @access  public
 * @author  Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class tao_models_classes_export_RdfExporter implements tao_models_classes_export_ExportHandler
{
    use EventManagerAwareTrait;

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return __('RDF');
    }

    /**
     * @inheritdoc
     */
    public function getExportForm(core_kernel_classes_Resource $resource)
    {
        if ($resource instanceof core_kernel_classes_Class) {
            $formData = ['class' => $resource];
        } else {
            $formData = ['instance' => $resource];
        }

        return (new tao_models_classes_export_RdfExportForm($formData))
            ->getForm();
    }

    /**
     * Run the export process.
     *
     * @param array $formValues
     * @param string $destination
     * @return string
     * @throws EasyRdf_Exception
     * @throws common_exception_Error
     * @throws Exception
     */
    public function export($formValues, $destination)
    {
        if (isset($formValues['filename'], $formValues['resource'])) {
            $class = new core_kernel_classes_Class($formValues['resource']);
            $adapter = new tao_helpers_data_GenerisAdapterRdf();
            $rdf = $adapter->export($class);

            if (!empty($rdf)) {
                $name = $formValues['filename'] . '_' . time() . '.rdf';
                $path = tao_helpers_File::concat([$destination, $name]);

                if (!tao_helpers_File::securityCheck($path, true)) {
                    throw new Exception('Unauthorized file name');
                }

                if (file_put_contents($path, $rdf)) {
                    $this->getEventManager()->trigger(new RdfExportEvent($class));

                    return $path;
                }
            }
        }

        return '';
    }

    /**
     * Exports an array of instances into an rdf string.
     *
     * @param array $instances
     * @return string
     */
    public function getRdfString($instances)
    {
        $api = core_kernel_impl_ApiModelOO::singleton();
        $rdf = '';
        $xmls = [];
        foreach ($instances as $instance) {
            $xmls[] = $api->getResourceDescriptionXML($instance->getUri());
        }

        if (count($xmls) === 1) {
            $rdf = $xmls[0];
        } elseif (count($xmls) > 1) {

            $baseDom = new DomDocument();
            $baseDom->formatOutput = true;
            $baseDom->loadXML($xmls[0]);

            for ($i = 1, $iMax = count($xmls); $i < $iMax; $i++) {

                $xmlDoc = new SimpleXMLElement($xmls[$i]);
                foreach ($xmlDoc->getNamespaces() as $nsName => $nsUri) {
                    if (!$baseDom->documentElement->hasAttribute('xmlns:' . $nsName)) {
                        $baseDom->documentElement->setAttribute('xmlns:' . $nsName, $nsUri);
                    }
                }
                $newDom = new DOMDocument();
                $newDom->loadXML($xmls[$i]);
                foreach ($newDom->getElementsByTagNameNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'Description') as $desc) {
                    $newNode = $baseDom->importNode($desc, true);
                    $baseDom->documentElement->appendChild($newNode);
                }
            }

            $rdf = $baseDom->saveXML();
        }

        return $rdf;
    }

}