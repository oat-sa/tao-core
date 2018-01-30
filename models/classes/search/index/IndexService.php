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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\search\index;

use oat\generis\model\OntologyRdfs;
use oat\oatbox\extension\script\MissingOptionException;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\Index;
use oat\tao\model\search\SearchService;
use oat\tao\model\search\SearchTokenGenerator;
use oat\tao\model\TaoOntology;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Class IndexService
 * @package oat\tao\model\search\index
 */
class IndexService extends ConfigurableService
{
    const SERVICE_ID = 'tao/IndexService';
    const OPTION_CUSTOM_REINDEX_CLASSES  = 'customReIndexClasses';
    const SUBSTITUTION_CONFIG_KEY = 'index_search_map';

    private $map;

    /**
     * @param IndexIterator $indexIterator
     * @return int
     * @throws \common_ext_InstallationException
     */
    public function fullReIndex(IndexIterator $indexIterator)
    {
        $searchService = SearchService::getSearchImplementation();
        if ($searchService->supportCustomIndex()) {
            SearchService::getSearchImplementation()->fullReIndex($indexIterator);
            $reIndexClasses = $this->getOption('customReIndexClasses');
            if ($reIndexClasses) {
                foreach ($reIndexClasses as $reIndexClass) {
                    if (file_exists($reIndexClass)) {
                        require_once $reIndexClass;
                    } elseif (class_exists($reIndexClass) && is_subclass_of($reIndexClass, 'oat\\oatbox\\action\\Action')) {
                        $action = new $reIndexClass();
                        if ($action instanceof ServiceLocatorAwareInterface) {
                            $action->setServiceLocator($this->getServiceLocator());
                        }
                        call_user_func($action, array());
                    } else {
                        throw new \common_ext_InstallationException('Unable to run install script '.$reIndexClass);
                    }
                }
            }
        }
    }

    /**
     * @param \core_kernel_classes_Resource $resource
     * @return IndexDocument
     * @throws \common_Exception
     * @throws \common_exception_InconsistentData
     */
    public function createDocumentFromResource(\core_kernel_classes_Resource $resource)
    {
        $tokenGenerator = new SearchTokenGenerator();

        $body = [];
        foreach ($tokenGenerator->generateTokens($resource) as $data) {
            /** @var Index $index */
            list($index, $strings) = $data;
            $body[$this->getIndexId($index)] = $strings;
        }
        $body['type_r'] = $this->getTypesForResource($resource);
        $this->updateIndexMap();
        $document = new IndexDocument(
            $resource->getUri(),
            $body
        );
        return $document;
    }

    /**
     * @param array $array
     * @return IndexDocument
     * @throws MissingOptionException
     * @throws \common_Exception
     */
    public function createDocumentFromArray($array = [])
    {
        if (!isset($array['id'])) {
            throw new MissingOptionException('Missed id property for the index document');
        }

        if (!isset($array['body'])) {
            throw new MissingOptionException('Missed body property for the index document');
        }
        $body = $array['body'];
        $newBody = [];
        $indexMap = $this->getOption(self::SUBSTITUTION_CONFIG_KEY);

        foreach ($body as $key => $value) {
            if (isset($indexMap[$key])) {
                $newBody[$indexMap[$key]] = $value;
            }
        }

        $document = new IndexDocument(
            $array['id'],
            $newBody
        );
        return $document;
    }

    /**
     * @param Index $index
     * @return mixed
     */
    public function getIndexId(Index $index) {
        if (!isset($this->map[$index->getIdentifier()])) {
            $suffix = $index->isFuzzyMatching() ? '_t' : '_s';
            if ($index->isDefaultSearchable()) {
                $suffix .= '_d';
            }
            $this->map[$index->getIdentifier()] = $index->getIdentifier().$suffix;
        }
        return $this->map[$index->getIdentifier()];
    }

    /**
     * @throws \common_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function updateIndexMap()
    {
        $indexMap = [];
        $options = $this->getOptions();
        if (isset($options[self::SUBSTITUTION_CONFIG_KEY])) {
            $indexMap = $options[self::SUBSTITUTION_CONFIG_KEY];
        }
        $indexMap = array_merge($indexMap, $this->map);
        if (!isset($indexMap['type'])) {
            $indexMap['type'] = 'type_r';
        }
        $options[self::SUBSTITUTION_CONFIG_KEY] = $indexMap;

        $this->getServiceManager()->register(self::SERVICE_ID,  new self($options));
    }

    /**
     * @param $resource
     * @return array
     */
    public function getTypesForResource($resource)
    {
        $toDo = array();
        foreach ($resource->getTypes() as $class) {
            $toDo[] = $class->getUri();
        }

        $done = array(OntologyRdfs::RDFS_RESOURCE, TaoOntology::CLASS_URI_OBJECT);
        $toDo = array_diff($toDo, $done);

        $classes = array();
        while (!empty($toDo)) {
            $class = new \core_kernel_classes_Class(array_pop($toDo));
            $classes[] = $class->getUri();
            foreach ($class->getParentClasses() as $parent) {
                if (!in_array($parent->getUri(), $done)) {
                    $toDo[] = $parent->getUri();
                }
            }
            $done[] = $class->getUri();
        }

        return $classes;
    }
}
