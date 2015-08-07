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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\model\export;

use core_kernel_classes_Resource;

/**
 * A custom Json LD exporter for single resources
 * that generates easily readable Json
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class JsonLdExport implements \JsonSerializable
{
    /**
     * Resoruce to export
     * @var core_kernel_classes_Resource
     */
    private $resource;
    
    /**
     * List of uris to exclude during export:
     * 
     * 'id' was introduced by bug
     * 
     * @var array
     */
    private $blackList = array(RDF_TYPE);

    public function getBlackList()
    {
    	return $this->blackList;
    }
    
    /**
     * Blacklist a property
     */
    public function blackList($propertyUri)
    {
        $this->blackList[] = $propertyUri;
    }
    
    public function __construct(core_kernel_classes_Resource $resource)
    {
        $this->resource = $resource;
    }

    public function jsonSerialize()
    {
        $triples = $this->resource->getRdfTriples()->toArray();
        foreach ($triples as $key => $triple) {
            if (in_array($triple->predicate, $this->getBlackList())) {
                unset($triples[$key]);
            }
        }
        
        $map = array();
        foreach ($triples as $triple) {
            if (! isset($map[$triple->predicate])) {
                $id = $this->generateId($triple->predicate);
                if (in_array($id, $map)) {
                    $nr = 0;
                    while (in_array($id . '_' . $nr, $map)) {
                        $nr ++;
                    }
                    $id = $id . '_' . $nr;
                }
                $map[$triple->predicate] = $id;
            }
        }
        
        $data = array(
            '@context' => array_flip($map),
            '@id' => $this->resource->getUri(),
            '@type' => $this->transfromArray($this->resource->getTypes())
        );
        
        foreach ($triples as $triple) {
            $key = $map[$triple->predicate];
            if (isset($data[$key])) {
                if (!is_array($data[$key])) {
                    $data[$key] = array($data[$key]);
                }
                $data[$key][] = $this->encodeValue($triple->object);
            } else {
                $data[$key] = $this->encodeValue($triple->object);
            }
        }
        return $data;
    }
    
    private function transfromArray($values)
    {
        if (count($values) > 1) {
            $encoded = array();
            foreach ($values as $value) {
                $encoded[] = $this->encodeValue($value);
            }
            return $encoded;
        } else {
            return $this->encodeValue(reset($values));
        }
    }
    
    protected function encodeValue($value)
    {
        return is_string($value)
            ? $value
            : ((is_object($value) && $value instanceof \core_kernel_classes_Resource)
                ? $value->getUri()
                : (string) $value
        ); 
    }
    
    protected function generateId($uri)
    {
        $property = new \core_kernel_classes_Property($uri);
        $label = strtolower(trim($property->getLabel()));
        $label = preg_replace(array('/\s/', '[^a-z\-]'), array('-', ''), $label);
        return empty($label) ? 'key' : $label;
    }
}