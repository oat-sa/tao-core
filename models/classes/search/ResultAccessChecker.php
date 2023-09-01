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
            $accessibleResources = $permissionHelper->filterByPermission(
                [$type->getUri()],
                PermissionInterface::RIGHT_READ
            );

            if (empty($accessibleResources)) {
                return false;
            }

            $class = $this->getClass($type->getUri());

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
            $accessibleResource = $permissionHelper
            ->filterByPermission(
                [$parentClass->getUri()],
                PermissionInterface::RIGHT_READ
            );

            if (empty($accessibleResource)) {
                return false;
            }

            if ($parentClass->getUri() === $topLevelClass->getUri()) {
                return true;
            }
        }
        return true;
    }

    private function getPermissionHelper(): PermissionHelper
    {
        return $this->getServiceLocator()->get(PermissionHelper::class);
    }
}
