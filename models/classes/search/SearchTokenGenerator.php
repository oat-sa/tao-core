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

use common_Logger;
use oat\tao\model\search\Search;
use oat\tao\model\search\Index;
use Solarium\Client;
use Solarium\QueryType\Update\Query\Document\DocumentInterface;
use oat\tao\model\search\tokenizer\ResourceTokenizer;
use oat\tao\model\search\tokenizer\PropertyValueTokenizer;

/**
 * Solarium Search implementation
 * 
 * Sample config
 * 
 *  $config = array(
 *      'endpoint' => array(
 *          'localhost' => array(
 *              'host' => '127.0.0.1',
 *             'port' => 8983,
 *             'path' => '/solr/',
 *          )
 *      )
 *  );
 * 
 * @author Joel Bout <joel@taotesting.com>
 */
class SearchTokenGenerator
{
    
    protected $indexMap = array();
    
    protected $propertyCache = array();

    /**
     * returns an array of subarrays containing [index, strings]
     * 
     * @param \core_kernel_classes_Resource $resource
     * @throws \common_exception_InconsistentData
     * @return array complex array
     */
     public function generateTokens(\core_kernel_classes_Resource $resource) {
        $tokens = array();
        foreach ($this->getProperties($resource) as $property) {
            $indexes = $this->getIndexes($property);
            if (!empty($indexes)) {
                $values = $resource->getPropertyValues($property);
                foreach ($indexes as $index) {
                    $tokenizer = $index->getTokenizer();
                    if ($tokenizer instanceof ResourceTokenizer) {
                        $strings = $tokenizer->getStrings($resource);
                    } elseif ($tokenizer instanceof PropertyValueTokenizer) {
                        $strings = $index->tokenize($values);
                    } else {
                        throw new \common_exception_InconsistentData('Unsupported tokenizer '.get_class($tokenizer));
                    }
                    $tokens[] = array($index, $strings);
                }
            }
        }
        return $tokens;
    }
    
    protected function getProperties(\core_kernel_classes_Resource $resource) {
        $classProperties = array(new \core_kernel_classes_Property(RDFS_LABEL));
        foreach ($resource->getTypes() as $type) {
            $classProperties = array_merge($classProperties, $this->getPropertiesByClass($type));
        }
        
        return $classProperties;
    }
    
    protected function getPropertiesByClass(\core_kernel_classes_Class $type) {
        if (!isset($this->propertyCache[$type->getUri()])) {
            $this->propertyCache[$type->getUri()] = $type->getProperties(true);
            // alternativly use non recursiv and union with getPropertiesByClass of parentclasses
        }
        return $this->propertyCache[$type->getUri()];
    }
    
    protected function getIndexes(\core_kernel_classes_Property $property) {
        if (!isset($this->indexMap[$property->getUri()])) {
            $this->indexMap[$property->getUri()] = array();
            $indexes = $property->getPropertyValues(new \core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAO.rdf#PropertyIndex'));
            foreach ($indexes as $indexUri) {
                $this->indexMap[$property->getUri()][] = new Index($indexUri);
            }
        }
        return $this->indexMap[$property->getUri()];
    }
}