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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA; *
 */

declare(strict_types=1);

namespace oat\tao\model\search\tokenizer;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;

class ResourceClasses implements PropertyValueTokenizer
{
    private const CLASSES_URIS_BLACK_LIST = [
        'http://www.tao.lu/Ontologies/TAO.rdf#AssessmentContentObject',
        'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject',
        'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource',
        'http://www.w3.org/2000/01/rdf-schema#Resource'
    ];

    /**
     * @param core_kernel_classes_Resource $resource
     * @return array
     */
    public function getStrings($resource)
    {
        $classes = [];

        foreach ($resource->getTypes() as $typeClass) {
            $classes[] = $typeClass->getLabel();

            foreach ($typeClass->getParentClasses(true) as $parentClass) {
                if ($this->hasReachedRootClass($parentClass)) {
                    return $classes;
                }

                $classes[] = $parentClass->getLabel();
            }
        }

        return $classes;
    }

    private function hasReachedRootClass(core_kernel_classes_Class $class): bool
    {
        return in_array($class->getUri(), self::CLASSES_URIS_BLACK_LIST, true);
    }
}
