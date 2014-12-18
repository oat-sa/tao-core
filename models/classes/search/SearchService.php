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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\tao\model\search;

use oat\tao\model\search\zend\ZendSearch;
use oat\tao\model\menu\MenuService;

/**
 * Search service
 * 
 * @author Joel Bout <joel@taotesting.com>
 */
class SearchService
{	
    const CONFIG_KEY = 'search';
    
    /**
     * 
     */
    static public function getSearchImplementation() 
    {
        $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $impl = $ext->getConfig(self::CONFIG_KEY);
        
        if ($impl === false || !$impl instanceof Search) {
            throw new \common_exception_Error('No valid Search implementation found');
        }
        
        return $impl;
    }

    /**
     * Store the search implementation to use
     * 
     * @param Search $impl
     */
    static public function setSearchImplementation(Search $impl) 
    {
        $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $ext->setConfig(self::CONFIG_KEY, $impl);
    }
    
    /**
     * @return int nr of resources indexed
     */
    static public function runIndexing() 
    {
        $iterator = new \core_kernel_classes_ResourceIterator(self::getIndexedClasses());
        return self::getSearchImplementation()->index($iterator);
    }
    
    /**
     * returns the root classes to index
     * 
     * @return array
     */
    static protected function getIndexedClasses() 
    {
        $classes = array();
        foreach (MenuService::getAllPerspectives() as $perspective) {
            foreach ($perspective->getChildren() as $structure) {
                foreach ($structure->getTrees() as $tree) {
                    if (!is_null($tree->get('rootNode'))) {
                        $classes[$tree->get('rootNode')] = new \core_kernel_classes_Class($tree->get('rootNode'));
                    }
                }
            }
        }
        
        return array_values($classes);
    }
    
    static public function getIndexes(\core_kernel_classes_Property $property) {
        $indexUris = $property->getPropertyValues(new \core_kernel_classes_Property(INDEX_PROPERTY));
        $indexes = array();
        
        foreach ($indexUris as $indexUri) {
            $indexes[] = new Index($indexUri);
        }
        
        return $indexes;
    }
    
    /**
     * Get the Search Indexes of a given $class.
     * 
     * The returned array is an associative array where keys are the Property URI
     * the Search Index belongs to, and the values are core_kernel_classes_Resource objects
     * corresponding to Search Index definitions.
     * 
     * @param \core_kernel_classes_Class $class
     * @param boolean $recursive Whether or not to look for Search Indexes that belong to sub-classes of $class. Default is true.
     * @return Index[] An array of Search Index to $class.
     */
    static public function getIndexesByClass(\core_kernel_classes_Class $class, $recursive = true)
    {
        $returnedIndexes = array();
        
        // Get properties to the root class hierarchy.
        $properties = $class->getProperties(true);
        
        foreach ($properties as $prop) {
            $propUri = $prop->getUri();
            $indexes = self::getIndexes($prop);
            
            if (count($indexes) > 0) {
                if (isset($returnedIndexes[$propUri]) === false) {
                    $returnedIndexes[$propUri] = array();
                }
                
                foreach ($indexes as $index) {
                    $returnedIndexes[$propUri][] = new Index($index->getUri());
                }
            }
        }
        
        return $returnedIndexes;
    }
}
