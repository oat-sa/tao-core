<?php

declare(strict_types=1);

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
 */

namespace oat\tao\model\export;

use core_kernel_classes_Resource;
use oat\generis\model\OntologyRdf;

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
     * @var \core_kernel_classes_ContainerCollection
     */
    private $triples;

    /**
     * @var \core_kernel_classes_Class[]
     */
    private $types;

    /** @var string */
    private $uri;

    /**
     * List of uris to exclude during export:
     *
     * @var array
     */
    private $blackList = [OntologyRdf::RDF_TYPE];

    private $encoders = [];

    /**
     * Create an Exported for the specified resurce
     *
     * @param core_kernel_classes_Resource $resource
     */
    public function __construct(core_kernel_classes_Resource $resource = null)
    {
        if ($resource !== null) {
            $this->setTriples($resource->getRdfTriples());
            $this->setTypes($resource->getTypes());
            $this->setUri($resource->getUri());
        }
    }

    /**
     * Blacklist a property
     *
     * @param string $propertyUri
     */
    public function blackList($propertyUri): void
    {
        $this->blackList[] = $propertyUri;
    }

    /**
     * @param \core_kernel_classes_ContainerCollection $triples
     */
    public function setTriples(\core_kernel_classes_ContainerCollection $triples): void
    {
        $this->triples = $triples;
    }

    /**
     * @param array $types
     */
    public function setTypes($types): void
    {
        $this->types = $types;
    }

    /**
     * @param string $uri
     */
    public function setUri($uri): void
    {
        $this->uri = $uri;
    }

    /**
     * (non-PHPdoc)
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        $data = [
            '@context' => [],
            '@id' => $this->uri,
        ];
        $types = $this->types;
        if (! empty($types)) {
            $data['@type'] = $this->transfromArray($types);
        }

        if (! $this->triples instanceof \core_kernel_classes_ContainerCollection) {
            return $data;
        }

        $triples = $this->triples->toArray();
        $map = [];
        foreach ($triples as $key => $triple) {
            if (in_array($triple->predicate, $this->blackList, true)) {
                continue;
            }

            if (! isset($map[$triple->predicate])) {
                $id = $this->generateId($triple->predicate);
                if (in_array($id, $map, true)) {
                    $nr = 0;
                    while (in_array($id . '_' . $nr, $map, true)) {
                        $nr++;
                    }
                    $id = $id . '_' . $nr;
                }
                $map[$triple->predicate] = $id;
                $data['@context'][$id] = $triple->predicate;
            }

            $key = $map[$triple->predicate];
            if (isset($data[$key])) {
                if (! is_array($data[$key])) {
                    $data[$key] = [$data[$key]];
                }
                $data[$key][] = $this->encodeValue($triple->object, $triple->predicate);
            } else {
                $data[$key] = $this->encodeValue($triple->object, $triple->predicate);
            }
        }

        // Enforce serialization to object if context is empty
        $data['@context'] = (object) $data['@context'];

        return $data;
    }

    public function registerEncoder($propertyUri, callable $encoder): void
    {
        $this->encoders[$propertyUri] = $encoder;
    }

    public function getEncoders()
    {
        return $this->encoders;
    }

    /**
     * Gets a list of properties to exclude
     *
     * @return array()
     */
    protected function getBlackList()
    {
        return $this->blackList;
    }

    /**
     * Encode the value in a json-ld compatible way
     *
     * @param mixed $value
     * @param string $propertyUri (optional) The URI of the property the $value is related to.
     * @return string
     */
    protected function encodeValue($value, $propertyUri = '')
    {
        $value = $this->applyEncoder($value, $propertyUri);

        return is_string($value)
            ? $value
            : (
                (is_object($value) && $value instanceof \core_kernel_classes_Resource)
                ? $value->getUri()
                : (string) $value
            );
    }

    /**
     * Generate a key for the property to use during export
     *
     * @param string $uri
     * @return string
     */
    protected function generateId($uri)
    {
        $property = new \core_kernel_classes_Property($uri);
        $label = strtolower(trim($property->getLabel()));
        $label = preg_replace(['/\s/', '[^a-z\-]'], ['-', ''], $label);
        return empty($label) ? 'key' : $label;
    }

    /**
     * Attempt to apply a specific value encoder.
     *
     * @param mixed $propertyUri
     * @param string (optional) The URI of the property the $propertyUri belongs to.
     * @return mixed
     */
    protected function applyEncoder($value, $propertyUri = '')
    {
        if (empty($propertyUri) === false) {
            $encoders = $this->getEncoders();

            if (isset($encoders[$propertyUri]) === true) {
                return call_user_func($encoders[$propertyUri], $value);
            }
        }

        return $value;
    }

    /**
     * Encode a values array
     *
     * @param array $values
     * @return mixed
     */
    private function transfromArray($values)
    {
        if (count($values) > 1) {
            $encoded = [];
            foreach ($values as $value) {
                $encoded[] = $this->encodeValue($value);
            }
            return $encoded;
        }
        return $this->encodeValue(reset($values));
    }
}
