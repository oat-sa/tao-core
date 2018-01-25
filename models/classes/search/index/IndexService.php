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

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\SearchService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Class IndexService
 * @package oat\tao\model\search\index
 */
class IndexService extends ConfigurableService
{
    const SERVICE_ID = 'tao/IndexService';
    const OPTION_CUSTOM_FIELDS  = 'custom_fields';
    const OPTION_ROOT_CLASSES  = 'rootClasses';
    const OPTION_CUSTOM_REINDEX_CLASSES  = 'customReIndexClasses';

    /**
     * @param $id
     * @param $type
     * @param null $responseId
     * @param array $body
     */
    public function addIndex($id, $type, $responseId = null, $body = [])
    {
        $document = new IndexDocument($id, $responseId, $type, $body);
        SearchService::getSearchImplementation()->index($document);
    }

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
     * @param $resource
     * @return mixed|null
     */
    public function getRootClassByResource($resource)
    {
        $types = $resource->getTypes();
        $rootClasses = $this->getOption(self::OPTION_ROOT_CLASSES);
        $rootClasses = array_keys($rootClasses);
        if ($types) {
            $classes = current($types)->getParentClasses(true);
            $classes = array_keys(array_merge($classes, $types));
            $compare = array_intersect($rootClasses, $classes);

            if ($compare) {
                $uri = current($compare);
                return $uri;
            }
            return null;
        }
        return null;
    }
}
