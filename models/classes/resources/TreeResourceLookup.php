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
use oat\tao\model\GenerisTreeFactory;
use oat\tao\helpers\TreeHelper;

/**
 * Look up resources and format them as a tree hierarchy
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class TreeResourceLookup extends ConfigurableService implements ResourceLookup
{
    const SERVICE_ID = 'tao/TreeResourceLookup';

    /**
     * Retrieve Resources in their hierarchy, for the given parameters as format them as tree.
     *
     * @param \core_kernel_classes_Class $rootClass       the resources class
     * @param array                      $propertyFilters propUri/propValue to search resources
     * @param string[]                   $selectedUris    the resources to open
     * @param int                        $offset          for paging
     * @param int                        $limit           for paging
     * @return array the resources
     */
    public function getResources(\core_kernel_classes_Class $rootClass, array $selectedUris = [], array $propertyFilters = [], $offset = 0, $limit = 30)
    {

        $openNodes = [];
        if(count($selectedUris) > 0){
            $openNodes = TreeHelper::getNodesToOpen($selectedUris, $rootClass);
        }
        if (!in_array($rootClass->getUri(), $openNodes)) {
            $openNodes[] = $rootClass->getUri();
        }
        $factory = new GenerisTreeFactory(true, $openNodes, $limit, $offset, $selectedUris, $propertyFilters);
        $treeData = $factory->buildTree($rootClass);

        return $this->formatTreeData([$treeData]);
    }


    /**
     * Reformat the the tree : state and count
     * Add the resource's categories
     * @param array $treeData
     * @return array the formated data
     */
    private function formatTreeData(array $treeData)
    {
        return array_map(function($data){

            $formated = [
                'label'    => $data['data'],
                'type'     => $data['type'],
                'uri'      => $data['attributes']['data-uri'],
                'classUri' => $data['attributes']['data-classUri'],
                'state'    => isset($data['state']) ? $data['state'] : false,
                'count'    => isset($data['count']) ? $data['count'] : 0
            ];
            if(isset($data['children'])){
                $formated['children'] = $this->formatTreeData($data['children']);
            }
            return $formated;
        }, $treeData);
    }
}
