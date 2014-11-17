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
namespace oat\tao\model\search\zend;

use oat\tao\model\search\Search;
use tao_models_classes_FileSourceService;
use common_Logger;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Search\QueryHit;

/**
 * Zend Index helper 
 * 
 * @author Joel Bout <joel@taotesting.com>
 */
class ZendIndexer
{	
    private $resource;
    
    public function __construct(\core_kernel_classes_Resource $resource)
    {
        $this->resource = $resource;
    }
    
    public function toDocument()
    {
        $document = new Document();
        common_Logger::i('indexing '.$this->resource->getLabel());
        
        $this->addUri($document);
        $this->indexTypes($document);
        foreach ($this->getIndexedProperties() as $property) {
            $this->indexProperty($document, $property);
        }
        
        return $document;
    }
    
    /**
     * Store uri, don't index it
     * 
     * @param Document $document
     */
    protected function addUri(Document $document)
    {
        $document->addField(Document\Field::unIndexed('uri', $this->resource->getUri()));
    }
    
    /**
     * @param Document $document
     */
    protected function indexTypes(Document $document)
    {
        $toDo = array();
        foreach ($this->resource->getTypes() as $class) {
            $toDo[] = $class->getUri();
            $document->addField(Document\Field::Text('class', $class->getLabel()));
        }
        
        $done = array(RDFS_CLASS, TAO_OBJECT_CLASS);
        $toDo = array_diff($toDo, $done);
        
        $classLabels = array();
        while (!empty($toDo)) {
            $class = new \core_kernel_classes_Class(array_pop($toDo));
            $classLabels[] = $class->getLabel();
            foreach ($class->getParentClasses() as $parent) {
                if (!in_array($parent->getUri(), $done)) {
                    $toDo[] = $parent->getUri();
                }
            }
            $done[] = $class->getUri();
        }
        $field = Document\Field::Keyword('class_r', $classLabels);
        $field->isStored = false;
        $document->addField($field);
    }
    
    protected function indexProperty(Document $document, \core_kernel_classes_Property $property)
    {
        \common_Logger::d('property '.$property->getLabel());
        
    	switch ($property->getUri()) {
    		case RDFS_LABEL:
    		    // if label: tokenize, store
    		    $document->addField(Document\Field::Text('label', $this->resource->getLabel()));
    		    break;
    		    
    		case 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel':
    		case 'http://myfantasy.domain/my_tao30.rdf#i1415962196740059':
    		    $this->indexKeyword($document, $property);
    		    break;
    		case 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent' :
    		    $content = \taoItems_models_classes_ItemsService::singleton()->getItemContent($this->resource);
    		    if (!empty($content)) {

    		        //if itemcontent: tokenize, nostore, complex data retrieval
    		        $document->addField(Document\Field::unStored('content', $content));
    		    }
    	}
    }
    
    /**
     * Keyword, no tokenisation, no storage, experimental multiple value support
     * 
     * @param Document $document
     * @param \core_kernel_classes_Property $property
     */
    protected function indexKeyword(Document $document, \core_kernel_classes_Property $property) {
        $val = array();
        foreach ($this->resource->getPropertyValues($property) as $value) {
            $valres = new \core_kernel_classes_Resource($value);
            $val[] = $valres->getLabel();
        }
        $field = Document\Field::Keyword('simple', $val);
        $field->isStored = false;
        $document->addField($field);
    }

    protected function getIndexedProperties()
    {
        $classProperties = array(new \core_kernel_classes_Property(RDFS_LABEL));
        foreach ($this->resource->getTypes() as $type) {
            $classProperties = array_merge($classProperties, \tao_helpers_form_GenerisFormFactory::getClassProperties($type));
        }
    
        return $classProperties;
    }

}