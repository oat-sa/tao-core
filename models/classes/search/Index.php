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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\tao\model\search;

use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\search\tokenizer\Tokenizer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Index extends \core_kernel_classes_Resource
{
    use OntologyAwareTrait;

    const RDF_TYPE = "http://www.tao.lu/Ontologies/TAO.rdf#Index";
    const TOKENIZER_CLASS = "http://www.tao.lu/Ontologies/TAO.rdf#TokenizerClass";

    /**
     * @return string
     * @throws \core_kernel_classes_EmptyProperty
     * @throws \core_kernel_classes_MultiplePropertyValuesException
     */
    public function getIdentifier()
    {
        return (string) $this->getUniquePropertyValue($this->getProperty(INDEX_PROPERTY_IDENTIFIER));
    }
    
    /**
     * @throws \common_exception_Error
     * @return Tokenizer
     */
    public function getTokenizer()
    {
        $tokenizerUri = $this->getUniquePropertyValue($this->getProperty(INDEX_PROPERTY_TOKENIZER));
        $tokenizer = $this->getResource($tokenizerUri);
        $implClass = (string) $tokenizer->getUniquePropertyValue($this->getProperty(self::TOKENIZER_CLASS));
        if (!class_exists($implClass)) {
            throw new \common_exception_Error('Tokenizer class "'.$implClass.'" not found for '.$tokenizer->getUri());
        }
        $tokenizer = new $implClass();
        if ($tokenizer instanceof ServiceLocatorAwareInterface) {
            $tokenizer->setServiceLocator($this->getServiceManager());
        }
        return $tokenizer;
    }

    /**
     * @param \core_kernel_classes_Resource $resource
     * @param $value
     * @return array
     * @throws \common_exception_Error
     */
    public function tokenize(\core_kernel_classes_Resource $resource, $value)
    {
        return $this->getTokenizer()->getStrings($resource, $value);
    }
    
    /**
     * Should the string matching be fuzzy
     * defaults to false if no information present
     * 
     * @return boolean
     */
    public function isFuzzyMatching()
    {
        $res = $this->getOnePropertyValue(new \core_kernel_classes_Property(INDEX_PROPERTY_FUZZY_MATCHING));
        return ! is_null($res) && is_object($res) && $res->getUri() == GENERIS_TRUE;
    }
    
    /**
     * Should the property be used by default if no index key is specified
     * defaults to false if no information present
     * 
     * @return boolean
     */
    public function isDefaultSearchable()
    {
        $res = $this->getOnePropertyValue($this->getProperty(INDEX_PROPERTY_DEFAULT_SEARCH));
        return ! is_null($res) && is_object($res) && $res->getUri() == GENERIS_TRUE;
    }
    
    
    /**
     * Should the value be stored
     * 
     * @return boolean
     */
    public function isStored()
    {
        return $this->getUri() === RDFS_LABEL;    
    }
}