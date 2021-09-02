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
 * Copyright (c) 2014-2021 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\search;

use common_exception_InconsistentData;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\index\OntologyIndex;
use oat\tao\model\search\tokenizer\ResourceTokenizer;
use oat\tao\model\search\tokenizer\PropertyValueTokenizer;
use oat\generis\model\OntologyAwareTrait;

/**
 * @author Joel Bout <joel@taotesting.com>
 */
class SearchTokenGenerator extends ConfigurableService
{
    use OntologyAwareTrait;

    /** @var OntologyIndex[][] */
    protected $indexMap = [];

    /** @var core_kernel_classes_Property[][] */
    protected $propertyCache = [];

    /**
     * returns an array of subarrays containing [index, strings]
     *
     * @throws common_exception_InconsistentData
     */
    public function generateTokens(core_kernel_classes_Resource $resource): array
    {
        $tokens = [];
        foreach ($this->getProperties($resource) as $property) {
            $indexes = $this->getIndexes($property);
            if (!empty($indexes)) {
                foreach ($indexes as $index) {
                    $tokenizer = $index->getTokenizer();
                    if ($tokenizer instanceof ResourceTokenizer) {
                        $strings = $tokenizer->getStrings($resource);
                    } elseif ($tokenizer instanceof PropertyValueTokenizer) {
                        $strings = $this->getStrings($resource, $property, $index, $tokenizer);
                    } else {
                        throw new common_exception_InconsistentData('Unsupported tokenizer ' . get_class($tokenizer));
                    }
                    $tokens[] = [$index, $strings];
                }
            }
        }
        return $tokens;
    }

    protected function getProperties(core_kernel_classes_Resource $resource)
    {
        $classProperties = [$this->getProperty(OntologyRdfs::RDFS_LABEL)];
        foreach ($resource->getTypes() as $type) {
            $classProperties = array_merge($classProperties, $this->getPropertiesByClass($type));
        }

        return $classProperties;
    }

    protected function getPropertiesByClass(\core_kernel_classes_Class $type)
    {
        if (!isset($this->propertyCache[$type->getUri()])) {
            $this->propertyCache[$type->getUri()] = $type->getProperties(true);
            // alternativly use non recursiv and union with getPropertiesByClass of parentclasses
        }
        return $this->propertyCache[$type->getUri()];
    }

    /**
     * @return OntologyIndex[]
     */
    protected function getIndexes(core_kernel_classes_Property $property): array
    {
        if (!isset($this->indexMap[$property->getUri()])) {
            $this->indexMap[$property->getUri()] = [];
            $indexes = $property->getPropertyValues($this->getProperty(OntologyIndex::PROPERTY_INDEX));
            foreach ($indexes as $indexUri) {
                $this->indexMap[$property->getUri()][] = new OntologyIndex($indexUri);
            }
        }
        return $this->indexMap[$property->getUri()];
    }

    private function getStrings(
        core_kernel_classes_Resource $resource,
        core_kernel_classes_Property $property,
        OntologyIndex $index,
        PropertyValueTokenizer $tokenizer
    ): array {
        if ($index->getIdentifier() !== 'class') {
            return $tokenizer->getStrings($resource->getPropertyValues($property));
        }

        $classes = [];

        foreach ($resource->getTypes() as $typeClass) {
            $classes[] = $typeClass->getLabel();

            foreach ($typeClass->getParentClasses(true) as $parentClass) {
                if ($parentClass->isClass()) {
                    $classes[] = $parentClass->getLabel();
                }

                //FIXME @TODO PoC: Find proper way to see if it is a root class
                if ($parentClass->getLabel() === 'Item') {
                    break;
                }
            }
        }

        //FIXME
//        echo PHP_EOL;
//        echo var_export($resource->getLabel(), true);
//        echo var_export($classes, true);
//        echo var_export(get_class($index->getTokenizer()), true);
//        echo var_export($index->getIdentifier(), true);
//        echo var_export($tokenizer->getStrings($resource->getPropertyValues($property)), true);
//        echo '____________________________________';
//        echo PHP_EOL;
//        echo PHP_EOL;
        //FIXME

        return $classes;
    }
}
