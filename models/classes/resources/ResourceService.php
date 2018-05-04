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

use \core_kernel_classes_Class;
use \core_kernel_classes_Resource;
use oat\generis\model\data\permission\PermissionInterface;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;

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
    public function getAllClasses(core_kernel_classes_Class $rootClass)
    {
        $result = [
            'uri'      => $rootClass->getUri(),
            'label'    => $rootClass->getLabel(),
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
     * Retrieve the classes for the given parameters
     * @param \core_kernel_classes_Class $resourceClass the resource class
     * @param string                     $format        the lookup format
     * @param string|array               $search        to filter by label if a string or provides the search filters
     * @param int                        $offset        for paging
     * @param int                        $limit         for paging
     * @return array the classes
     */
    public function getClasses(\core_kernel_classes_Class $rootClass, $format = 'list', $selectedUris = [], $search = '', $offset = 0, $limit = 30)
    {
        $propertyFilters = $this->getPropertyFilters($search);

        $result = [];

        $resourceLookup = $this->getResourceLookup($format);
        if (!is_null($resourceLookup)) {
            $result = $resourceLookup->getClasses($rootClass, $selectedUris, $propertyFilters, $offset, $limit);
        }
        return $result;
    }

    /**
     * Get the permissions for a list of resources.
     *
     * @param User $user the user to check the permissions
     * @param array $resources the resources to get the permissions
     * @return array the available rights and the permissions per resource
     */
    public function getResourcesPermissions(User $user, $resources)
    {
        $permissions = [];
        if(!is_null($user)){
            try {
                $permissionManager = $this->getServiceManager()->get(PermissionInterface::SERVICE_ID);
                $supportedRights   = $permissionManager->getSupportedRights();
                $permissions['supportedRights'] = $supportedRights;

                if(count($supportedRights) > 0){
                    $uris = $this->getUris($resources);

                    $permissions['data'] = $permissionManager->getPermissions($user, $uris);
                }
            } catch(\Exception $e){
                \common_Logger::w('Unable to retrieve permssions ' . $e->getMessage());
            }
        }
        return $permissions;

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

    /**
     * Walk through the resources (recursively) to get theirs URIs
     *
     * @param array|core_kernel_class_Resource $nodes the tree or a sub tree, a resource or a resource list
     * @return string[] the list of URIs
     */
    private function getUris($nodes)
    {
        $uris = [];

        if ($nodes instanceof core_kernel_classes_Resource) {
            $uris[] = $nodes->getUri();
        }
        if(is_array($nodes)){

            //legacy format
            if (isset($nodes['attributes']['data-uri'])) {
                $uris[] = $nodes['attributes']['data-uri'];
            }
            if (isset($nodes['uri'])) {
                $uris[] = $nodes['uri'];
            }

            $treeKeys = array_keys($nodes);
            if (isset($treeKeys[0]) && is_int($treeKeys[0])) {
                foreach ($nodes as $node) {
                    $uris = array_merge($uris, $this->getUris($node));

                }
            }

            if (isset($nodes['children'])) {
                $uris = array_merge($uris, $this->getUris($nodes['children']));
            }
        }

        return $uris;
    }
}
