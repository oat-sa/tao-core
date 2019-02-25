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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2017 (update and modification) Open Assessment Technologies SA
 *
 */

use oat\generis\model\OntologyRdf;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\upload\UploadService;
use oat\oatbox\filesystem\File;

/**
 * Adapter for RDF/RDFS format
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 */
class tao_helpers_data_GenerisAdapterRdf extends tao_helpers_data_GenerisAdapter
{

    /**
     * Import a XML file as is into the ontology
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string $source
     * @param  core_kernel_classes_Class $destination
     * @param  string $namespace
     * @return boolean
     * @throws \oat\oatbox\service\ServiceNotFoundException
     * @throws \common_Exception
     */
    public function import($source, core_kernel_classes_Class $destination = null, $namespace = null)
    {
        /** @var UploadService $uploadService */
        $uploadService = ServiceManager::getServiceManager()->get(UploadService::SERVICE_ID);
        if (!$source instanceof File) {
            $file = $uploadService->getUploadedFlyFile($source);
        } else {
            $file = $source;
        }

        $returnValue = false;

        if ($file->exists()) {
            $api = core_kernel_impl_ApiModelOO::singleton();
            if ($destination !== null) {
                $targetNamespace = substr($destination->getUri(), 0, strpos($destination->getUri(), '#'));
            } elseif ($namespace !== null) {
                $targetNamespace = $namespace;
            } else {
                $targetNamespace = rtrim(common_ext_NamespaceManager::singleton()->getLocalNamespace()->getUri(), '#');
            }
            $returnValue = $api->importXmlRdf($targetNamespace, $file);
        }

        $uploadService->remove($file);

        return $returnValue;
    }

    /**
     * Export to xml-rdf the ontology of the Class in parameter.
     * All the ontologies are exported if the class is not set
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param core_kernel_classes_Class|null $source
     * @return string
     * @throws EasyRdf_Exception
     */
    public function export(core_kernel_classes_Class $source = null)
    {
        if ($source === null) {
            return core_kernel_api_ModelExporter::exportAll();
        }

        $graph = new EasyRdf_Graph();
        if ($source->isClass()) {
            $this->addClass($graph, $source);
        } else {
            $this->addResource($graph, $source);
        }
        $format = EasyRdf_Format::getFormat('rdfxml');
        return $graph->serialise($format);
    }

    /**
     * Add a class to the graph
     *
     * @param EasyRdf_Graph $graph
     * @param core_kernel_classes_Class $resource
     * @ignore
     */
    private function addClass(EasyRdf_Graph $graph, core_kernel_classes_Class $resource)
    {
        $this->addResource($graph, $resource);
        foreach ($resource->getInstances() as $instance) {
            $this->addResource($graph, $instance);
        }
        foreach ($resource->getSubClasses() as $subclass) {
            $this->addClass($graph, $subclass);
        }
        foreach ($resource->getProperties() as $property) {
            $this->addResource($graph, $property);
        }

    }

    /**
     * Add a resource to the graph
     *
     * @param EasyRdf_Graph $graph
     * @param core_kernel_classes_Resource $resource
     * @ignore
     */
    private function addResource(EasyRdf_Graph $graph, core_kernel_classes_Resource $resource)
    {
        foreach ($resource->getRdfTriples() as $triple) {
            $language = !empty($triple->lg) ? $triple->lg : null;
            if (common_Utils::isUri($triple->object)) {
                if ($triple->predicate !== OntologyRdf::RDF_TYPE && strpos($triple->object, LOCAL_NAMESPACE) !== false) {
                    continue;
                }
                $graph->add($triple->subject, $triple->predicate, $triple->object);
            } else {
                if ($this->isSerializedFile($triple->object)) {
                    continue;
                }
                $graph->addLiteral($triple->subject, $triple->predicate, $triple->object, $language);
            }
        }
    }

    /**
     * Check if the given object is a serialized file reference
     *
     * @param string $object
     * @return bool
     * @see \oat\generis\model\fileReference\UrlFileSerializer::unserialize
     */
    private function isSerializedFile($object)
    {
        $isFile = false;
        $type = substr($object, 0, strpos($object, ':'));
        if (in_array($type, ['file', 'dir'])) {
            $isFile = true;
        }

        return $isFile;
    }
}
