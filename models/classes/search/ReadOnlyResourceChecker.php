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

class ReadOnlyResourceChecker extends ConfigurableService
{
    use OntologyAwareTrait;

    public function get(array $result, PermissionHelper $permissionHelper): array
    {
        foreach ($result as $content) {
            $isAccessible = ($content['label'] !== "Access Denied") ? true : false;

            $resource = $this->getResource($content['id']);
    
            $readonly = false;
    
            $topLevelClass = $this->getClass(TaoOntology::CLASS_URI_OBJECT);
    
            foreach ($resource->getTypes() as $type) {
                $accessibleResources = $permissionHelper
                ->filterByPermission(
                    [$type->getUri()],
                    PermissionInterface::RIGHT_READ
                );
    
                if (empty($accessibleResources) || !$isAccessible) {
                    $readonly = true;
                    break;
                }
    
                $class = $this->getClass($type->getUri());
                $readonly = $this->checkParentClassPermission($class, $permissionHelper, $topLevelClass);
    
                if ($readonly === true) {
                    break;
                }
            }
    
            if ($readonly === true) {
                $content['id'] = '';
            }
    
            $readOnlyResources[$content['id']] = $readonly;
        }

        return $readOnlyResources;
    }

    private function checkParentClassPermission(core_kernel_classes_Class $class, PermissionHelper $permissionHelper, core_kernel_classes_Class $topLevelClass): bool
    {
        $parentClasses = $class->getParentClasses(true);

        foreach ($parentClasses as $parentClass) {
            $accessibleResource = $permissionHelper
            ->filterByPermission(
                [$parentClass->getUri()],
                PermissionInterface::RIGHT_READ
            );

            if (empty($accessibleResource)) {
                return true;
            }

            if ($parentClass->getUri() === $topLevelClass->getUri()) {
                return false;
            }
        }
        return false;
    }
}
