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
use oat\tao\model\search\tokenizer\ResourceClasses;
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
     * @throws common_exception_InconsistentData
     *
     * @return array[][] Returns an array of subArrays containing [index, strings]
     */
    public function generateTokens(core_kernel_classes_Resource $resource): array
    {
        $tokens = [];

        foreach ($this->getProperties($resource) as $property) {
            $indexes = $this->getIndexes($property);

            if (!empty($indexes)) {
                foreach ($indexes as $index) {
                    $tokens[] = [$index, $this->getTokenizerStrings($index, $resource, $property)];
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

    private function getTokenizerStrings(
        OntologyIndex $index,
        core_kernel_classes_Resource $resource,
        core_kernel_classes_Property $property
    ): array {
        $tokenizer = $index->getTokenizer();

        if ($tokenizer instanceof ResourceTokenizer) {
            return $tokenizer->getStrings($resource);
        }

        if ($tokenizer instanceof PropertyValueTokenizer) {
            return $tokenizer instanceof ResourceClasses
                ? $tokenizer->getStrings($resource)
                : $tokenizer->getStrings($resource->getPropertyValues($property));
        }

        throw new common_exception_InconsistentData(
            sprintf(
                'Unsupported tokenizer %s',
                get_class($tokenizer)
            )
        );
    }
}
