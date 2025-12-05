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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search;

use core_kernel_classes_Class;
use oat\generis\model\data\permission\PermissionHelper;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\TaoOntology;

class ResultAccessChecker extends ConfigurableService
{
    use OntologyAwareTrait;

    public function hasReadAccess(array $content): bool
    {
        $resource = $this->getResource($content['id']);

        $topLevelClass = $this->getClass(TaoOntology::CLASS_URI_OBJECT);

        $permissionHelper =  $this->getPermissionHelper();

        foreach ($resource->getTypes() as $type) {
            $typeUri = $type->getUri();

            // Skip permission check for ontology classes (schema/metadata)
            // These don't have permission records in the database
            if ($this->isOntologyClass($typeUri)) {
                continue;
            }

            $accessibleResources = $permissionHelper->filterByPermission(
                [$typeUri],
                PermissionInterface::RIGHT_READ
            );

            if (empty($accessibleResources)) {
                return false;
            }

            $class = $this->getClass($typeUri);

            if (!$this->hasReadPermissionForClass($class, $permissionHelper, $topLevelClass)) {
                return false;
            }
        }

        return true;
    }

    private function hasReadPermissionForClass(
        core_kernel_classes_Class $class,
        PermissionHelper $permissionHelper,
        core_kernel_classes_Class $topLevelClass
    ): bool {
        $parentClasses = $class->getParentClasses(true);

        foreach ($parentClasses as $parentClass) {
            $parentUri = $parentClass->getUri();

            // Skip permission check for ontology classes (schema/metadata)
            if ($this->isOntologyClass($parentUri)) {
                // If we reached the top level class, we're done
                if ($parentUri === $topLevelClass->getUri()) {
                    return true;
                }
                continue;
            }

            $accessibleResource = $permissionHelper
                ->filterByPermission(
                    [$parentUri],
                    PermissionInterface::RIGHT_READ
                );

            if (empty($accessibleResource)) {
                return false;
            }

            if ($parentUri === $topLevelClass->getUri()) {
                return true;
            }
        }
        return true;
    }

    private function getPermissionHelper(): PermissionHelper
    {
        return $this->getServiceLocator()->get(PermissionHelper::class);
    }

    /**
     * Check if a URI represents an ontology class (schema/metadata) rather than a data instance.
     * Ontology classes don't have permission records in the database.
     *
     * @param string $uri
     * @return bool
     */
    private function isOntologyClass(string $uri): bool
    {
        // Ontology classes are in specific namespaces
        $ontologyNamespaces = [
            'http://www.tao.lu/Ontologies/',
            'http://www.w3.org/2000/01/rdf-schema#',
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        ];

        foreach ($ontologyNamespaces as $namespace) {
            if (strpos($uri, $namespace) === 0) {
                return true;
            }
        }

        return false;
    }
}
