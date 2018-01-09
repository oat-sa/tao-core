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
namespace oat\tao\model\resources;

use oat\oatbox\service\ConfigurableService;
use \core_kernel_classes_Class;

/**
 * This service let's you access resources
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class ResourceService extends ConfigurableService
{
    const SERVICE_ID = 'tao/ResourceService';

    const LABEL_URI  = 'http://www.w3.org/2000/01/rdf-schema#label';

    /**
     * The different lookup formats
     */
    private static $formats = [ 'list', 'tree'];

    /**
     * The lookup instances by format
     */
    private $lookups;

    /**
     * Get the list of  classes from the given root class
     * @param core_kernel_classes_Class $rootClass the root class
     * @return array the classes hierarchy
     */
    public function getClasses(core_kernel_classes_Class $rootClass)
    {
        $result = [
            'uri' => $rootClass->getUri(),
            'label' => $rootClass->getLabel(),
            'children' => $this->getSubClasses($rootClass->getSubClasses(false))
        ];

        return $result;
    }

    /**
     * Get the class subclasses
     * @return array the classes hierarchy
     */
    private function getSubClasses($subClasses)
    {
        $result = [];

        foreach ($subClasses as $subClass) {
            $children = $subClass->getSubClasses(false);
            $entry = [
                'uri' => $subClass->getUri(),
                'label' => $subClass->getLabel()
            ];
            if (count($children) > 0) {
                $entry['children'] = $this->getSubClasses($children);
            }
            array_push($result, $entry);
        }

        return $result;
    }

    /**
     * Retrieve the resources for the given parameters
     * @param \core_kernel_classes_Class $resourceClass the resource class
     * @param string                     $format        the lookup format
     * @param string|array               $search        to filter by label if a string or provides the search filters
     * @param int                        $offset        for paging
     * @param int                        $limit         for paging
     * @return array the resources
     */
    public function getResources(\core_kernel_classes_Class $rootClass, $format = 'list', $selectedUris = [], $search = '', $offset = 0, $limit = 30)
    {
        $propertyFilters = $this->getPropertyFilters($search);

        $result = [];

        $resourceLookup = $this->getResourceLookup($format);
        if (!is_null($resourceLookup)) {
            $result = $resourceLookup->getResources($rootClass, $selectedUris, $propertyFilters, $offset, $limit);
        }
        return $result;
    }

    /**
     * Get the filters based on the search param
     *
     * @param string|array  $search to filter by label if a string or provides the search filters
     * @return array the list of property filters
     */
    private function getPropertyFilters($search = '')
    {
        $propertyFilters = [];

        if(is_string($search) && strlen(trim($search)) > 0){
            $propertyFilters[self::LABEL_URI] = $search;
        }
        if(is_array($search)){
            foreach($search as $uri => $value){
                if( is_string($uri) &&
                    (is_string($value) && strlen(trim($value)) > 0) ||
                    (is_array($value) && count($value) > 0) ) {
                    $propertyFilters[$uri] = $value;
                }
            }
        }
        return $propertyFilters;
    }

    /**
     * Get the resource lookup for the given format
     * @return ResourceLookup or null
     */
    private function getResourceLookup($format)
    {
        if(in_array($format, self::$formats)){
            if(!isset($this->lookups)){
                $this->lookups = [
                    'list' => $this->getServiceManager()->get(ListResourceLookup::SERVICE_ID),
                    'tree' => $this->getServiceManager()->get(TreeResourceLookup::SERVICE_ID)
                ];
            }
            return $this->lookups[$format];
        }
        return null;
    }
}
