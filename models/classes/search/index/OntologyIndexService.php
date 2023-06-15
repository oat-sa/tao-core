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

namespace oat\tao\model\search\index;

use core_kernel_classes_Class;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\TaoOntology;

/**
 * Index service
 *
 * @author Joel Bout <joel@taotesting.com>
 */
class OntologyIndexService
{
    /**
     * Create a new index
     *
     * @param \core_kernel_classes_Property $property
     * @param unknown $identifier
     * @param \core_kernel_classes_Resource $tokenizer
     * @param unknown $isFuzzyMatching
     * @param unknown $isDefaultSearchable
     * @return OntologyIndex
     */
    public static function createIndex(
        \core_kernel_classes_Property $property,
        $identifier,
        \core_kernel_classes_Resource $tokenizer,
        $isFuzzyMatching,
        $isDefaultSearchable
    ) {
        $class = new \core_kernel_classes_Class(OntologyIndex::RDF_TYPE);
        $existingIndex = self::getIndexById($identifier);
        if (!is_null($existingIndex)) {
            throw new \common_Exception('Index ' . $identifier . ' already in use');
        }
        // verify identifier is unused
        $resource = $class->createInstanceWithProperties([
            OntologyRdfs::RDFS_LABEL => $identifier,
            OntologyIndex::PROPERTY_INDEX_IDENTIFIER => $identifier,
            OntologyIndex::PROPERTY_INDEX_TOKENIZER => $tokenizer,
            OntologyIndex::PROPERTY_INDEX_FUZZY_MATCHING => $isFuzzyMatching
                ? GenerisRdf::GENERIS_TRUE
                : GenerisRdf::GENERIS_FALSE,
            OntologyIndex::PROPERTY_DEFAULT_SEARCH => $isDefaultSearchable
                ? GenerisRdf::GENERIS_TRUE
                : GenerisRdf::GENERIS_FALSE
        ]);
        $property->setPropertyValue(new \core_kernel_classes_Property(OntologyIndex::PROPERTY_INDEX), $resource);
        return new OntologyIndex($resource);
    }

    /**
     * Get an index by its unique index id
     *
     * @param string $identifier
     * @throws \common_exception_InconsistentData
     * @return OntologyIndex
     */
    public static function getIndexById($identifier)
    {

        $indexClass = new core_kernel_classes_Class(OntologyIndex::RDF_TYPE);
        $resources = $indexClass->searchInstances([
            OntologyIndex::PROPERTY_INDEX_IDENTIFIER  => $identifier
            ], ['like' => false]);
        if (count($resources) > 1) {
            throw new \common_exception_InconsistentData("Several index exist with the identifier " . $identifier);
        }
        return count($resources) > 0
            ? new OntologyIndex(array_shift($resources))
            : null;
    }

    /**
     * Get all indexes of a property
     *
     * @param \core_kernel_classes_Property $property
     * @return multitype:OntologyIndex
     */
    public static function getIndexes(\core_kernel_classes_Property $property)
    {
        $indexUris = $property->getPropertyValues(new \core_kernel_classes_Property(OntologyIndex::PROPERTY_INDEX));
        $indexes = [];

        foreach ($indexUris as $indexUri) {
            $indexes[] = new OntologyIndex($indexUri);
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
     * @param boolean $recursive Whether or not to look for Search Indexes that belong to sub-classes of $class.
     *                           Default is true.
     * @return OntologyIndex[] An array of Search Index to $class.
     */
    public static function getIndexesByClass(\core_kernel_classes_Class $class, $recursive = true)
    {
        $returnedIndexes = [];

        // Get properties to the root class hierarchy.
        $properties = $class->getProperties(true);

        foreach ($properties as $prop) {
            $propUri = $prop->getUri();
            $indexes = self::getIndexes($prop);

            if (count($indexes) > 0) {
                if (isset($returnedIndexes[$propUri]) === false) {
                    $returnedIndexes[$propUri] = [];
                }

                foreach ($indexes as $index) {
                    $returnedIndexes[$propUri][] = new OntologyIndex($index->getUri());
                }
            }
        }

        return $returnedIndexes;
    }
}
