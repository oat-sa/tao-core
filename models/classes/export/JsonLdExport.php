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
 * Copyright (c) 2015-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\export;

use core_kernel_classes_Class;
use core_kernel_classes_ContainerCollection;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use core_kernel_classes_Triple;
use JsonSerializable;
use oat\generis\model\data\Ontology;
use oat\generis\model\OntologyRdf;
use oat\tao\model\export\Metadata\JsonLd\JsonLdTripleEncoderInterface;

/**
 * A custom Json LD exporter for single resources
 * that generates easily readable Json
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class JsonLdExport implements JsonSerializable
{
    /** @var core_kernel_classes_ContainerCollection */
    private $triples;

    /** @var Ontology|null */
    private $ontology;

    /** @var core_kernel_classes_Class[] */
    private $types;

    /** @var string */
    private $uri;

    /** @var array */
    private $blackList = [
        OntologyRdf::RDF_TYPE
    ];

    /** @var array */
    private $encoders = [];

    /** @var JsonLdTripleEncoderInterface[] */
    private $tripleEncoders = [];

    /**
     * @deprecated Do not use $resource in the constructor, use setResource instead.
     *             This class is now instantiated in the DI container.
     */
    public function __construct(core_kernel_classes_Resource $resource = null, Ontology $ontology = null)
    {
        if (!is_null($resource)) {
            $this->setResource($resource);
        }

        $this->ontology = $ontology;
    }

    public function setResource(core_kernel_classes_Resource $resource): self
    {
        $this->setTriples($resource->getRdfTriples());
        $this->setTypes($resource->getTypes());
        $this->setUri($resource->getUri());

        return $this;
    }

    public function setTriples(core_kernel_classes_ContainerCollection $triples): self
    {
        $this->triples = $triples;

        return $this;
    }

    /**
     * @param array $types
     */
    public function setTypes($types): self
    {
        $this->types = $types;

        return $this;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function blackList(string $propertyUri): void
    {
        $this->blackList[] = $propertyUri;
    }

    public function registerEncoder($propertyUri, callable $encoder): void
    {
        $this->encoders[$propertyUri] = $encoder;
    }

    public function getEncoders(): array
    {
        return $this->encoders;
    }

    public function addTripleEncoder(JsonLdTripleEncoderInterface $encoder): self
    {
        $this->tripleEncoders[get_class($encoder)] = $encoder;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $data = [
            '@context' => [],
            '@id' => $this->uri,
        ];

        if (!empty($this->types)) {
            $data['@type'] = $this->encodeValues($this->types);
        }

        if (!$this->triples instanceof core_kernel_classes_ContainerCollection) {
            return $data;
        }

        /** @var core_kernel_classes_Triple[] $triples */
        $triples = $this->triples->toArray();
        $map = [];

        foreach ($triples as $key => $triple) {
            if (in_array($triple->predicate, $this->blackList)) {
                continue;
            }

            if (!isset($map[$triple->predicate])) {
                $id = $this->generateId($triple->predicate);
                if (in_array($id, $map)) {
                    $nr = 0;
                    while (in_array($id . '_' . $nr, $map)) {
                        $nr++;
                    }
                    $id = $id . '_' . $nr;
                }
                $map[$triple->predicate] = $id;
                $data['@context'][$id] = $triple->predicate;
            }

            $key = $map[$triple->predicate];
            if (isset($data[$key])) {
                if (!is_array($data[$key])) {
                    $data[$key] = [$data[$key]];
                }
                $data[$key][] = $this->encodeValue($triple->object, $triple->predicate);
            } else {
                $data[$key] = $this->encodeValue($triple->object, $triple->predicate);
            }
        }

        $data = $this->encodeTriples($data, ...$triples);

        // Enforce serialization to object if context is empty
        $data['@context'] = (object) $data['@context'];

        return $data;
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
        $property = $this->ontology
            ? $this->ontology->getProperty($uri)
            : new core_kernel_classes_Property($uri);

        $label = strtolower(trim($property->getLabel()));
        $label = preg_replace(['/\s/', '[^a-z\-]'], ['-', ''], $label);

        return empty($label) ? 'key' : $label;
    }

    /**
     * Attempt to apply a specific value encoder.
     *
     * @param mixed $value
     * @param string (optional) The URI of the property the $value belongs to.
     * @return mixed
     */
    protected function applyEncoder($value, $propertyUri = '')
    {
        if (empty($propertyUri) === false) {
            $encoders = $this->getEncoders();

            if (isset($encoders[$propertyUri]) === true) {
                $encodedValue = call_user_func($encoders[$propertyUri], $value);
                return $encodedValue;
            }
        }

        return $value;
    }

    private function encodeTriples(array $data, core_kernel_classes_Triple ...$triples): array
    {
        foreach ($this->tripleEncoders as $tripleEncoder) {
            foreach ($triples as $triple) {
                $data = $tripleEncoder->encode($data, $triple);
            }
        }

        return $data;
    }

    /**
     * @return string|array
     */
    private function encodeValues(array $values)
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
