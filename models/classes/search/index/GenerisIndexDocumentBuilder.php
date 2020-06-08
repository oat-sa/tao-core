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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\index;

use oat\generis\model\OntologyRdfs;
use oat\tao\model\search\SearchTokenGenerator;
use oat\tao\model\TaoOntology;

class GenerisIndexDocumentBuilder extends AbstractIndexDocumentBuilder
{
    /** @var array */
    private $map;

    /**
     * {@inheritdoc}
     */
    public function createDocumentFromResource(\core_kernel_classes_Resource $resource): IndexDocument
    {
        $tokenGenerator = new SearchTokenGenerator();

        $body = [];
        $indexesProperties = [];
        foreach ($tokenGenerator->generateTokens($resource) as $data) {
            /** @var OntologyIndex $index */
            list($index, $strings) = $data;
            $body[$index->getIdentifier()] = $strings;
            $indexesProperties[$index->getIdentifier()] = $this->getIndexProperties($index);
        }

        $body['type'] = $this->getTypesForResource($resource);

        $document = new IndexDocument(
            $resource->getUri(),
            $body,
            $indexesProperties
        );

        return $document;
    }

    /**
     * @param OntologyIndex $index
     * @return IndexProperty
     * @throws \common_Exception
     */
    public function getIndexProperties(OntologyIndex $index): IndexProperty
    {
        if (!isset($this->map[$index->getIdentifier()])) {
            $indexProperty = new IndexProperty(
                $index->getIdentifier(),
                $index->isFuzzyMatching(),
                $index->isDefaultSearchable()
            );
            $this->map[$index->getIdentifier()] = $indexProperty;
        }

        return $this->map[$index->getIdentifier()];
    }

    /**
     * @param $resource
     * @return string[]
     * @throws \common_exception_Error
     */
    private function getTypesForResource(\core_kernel_classes_Resource $resource): array
    {
        $toDo = [];
        foreach ($resource->getTypes() as $class) {
            $toDo[] = $class->getUri();
        }

        $done = [OntologyRdfs::RDFS_RESOURCE, TaoOntology::CLASS_URI_OBJECT];
        $toDo = array_diff($toDo, $done);

        $classes = [];
        while (!empty($toDo)) {
            $class = new \core_kernel_classes_Class(array_pop($toDo));
            $classes[] = $class->getUri();
            foreach ($class->getParentClasses() as $parent) {
                if (!in_array($parent->getUri(), $done)) {
                    $toDo[] = $parent->getUri();
                }
            }
            $done[] = $class->getUri();
        }

        return $classes;
    }
}
