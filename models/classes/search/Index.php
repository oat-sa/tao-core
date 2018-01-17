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

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\TaoOntology;

class Index extends \core_kernel_classes_Resource {

    const RDF_TYPE = "http://www.tao.lu/Ontologies/TAO.rdf#Index";
    const PROPERTY_INDEX = 'http://www.tao.lu/Ontologies/TAO.rdf#PropertyIndex';
    const PROPERTY_INDEX_FUZZY_MATCHING = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexFuzzyMatching';
    const PROPERTY_INDEX_IDENTIFIER = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexIdentifier';
    const PROPERTY_INDEX_TOKENIZER = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexTokenizer';
    const PROPERTY_DEFAULT_SEARCH = 'http://www.tao.lu/Ontologies/TAO.rdf#IndexDefaultSearch';

    private $cached = null;

    /**
     * Preload all the index properties and return the
     * property requested
     *
     * @param string $propertyUri
     * @return Ambigous <NULL, mixed>
     */
    private function getOneCached($propertyUri)
    {
        if (is_null($this->cached)) {
            $props = array(static::PROPERTY_INDEX_IDENTIFIER, static::PROPERTY_INDEX_TOKENIZER, static::PROPERTY_INDEX_FUZZY_MATCHING, static::PROPERTY_DEFAULT_SEARCH);
            $this->cached = $this->getPropertiesValues($props);
        }
        return empty($this->cached[$propertyUri]) ? null : reset($this->cached[$propertyUri]);
    }

    public function getIdentifier()
    {
        return (string)$this->getOneCached(static::PROPERTY_INDEX_IDENTIFIER);
    }

    /**
     * @throws \common_exception_Error
     * @return oat\tao\model\search\tokenizer\Tokenizer
     */
    public function getTokenizer()
    {
        $tokenizer = $this->getOneCached(static::PROPERTY_INDEX_TOKENIZER);
        $implClass = (string)$tokenizer->getUniquePropertyValue($this->getProperty("http://www.tao.lu/Ontologies/TAO.rdf#TokenizerClass"));
        if (!class_exists($implClass)) {
            throw new \common_exception_Error('Tokenizer class "'.$implClass.'" not found for '.$tokenizer->getUri());
        }
        return new $implClass();
    }

    /**
     * Should the string matching be fuzzy
     * defaults to false if no information present
     *
     * @return boolean
     */
    public function isFuzzyMatching()
    {
        $res = $this->getOneCached(static::PROPERTY_INDEX_FUZZY_MATCHING);
        return !is_null($res) && is_object($res) && $res->getUri() == GenerisRdf::GENERIS_TRUE;
    }

    /**
     * Should the property be used by default if no index key is specified
     * defaults to false if no information present
     *
     * @return boolean
     */
    public function isDefaultSearchable()
    {
        $res = $this->getOneCached(static::PROPERTY_DEFAULT_SEARCH);
        return !is_null($res) && is_object($res) && $res->getUri() == GenerisRdf::GENERIS_TRUE;
    }


    /**
     * Should the value be stored
     *
     * @return boolean
     */
    public function isStored()
    {
        return $this->getUri() === OntologyRdfs::RDFS_LABEL;
    }
}
