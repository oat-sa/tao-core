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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\role;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\role\contract\RoleContractInterface;

class AddRoleService extends ConfigurableService implements AddRoleServiceInterface
{
    const SERVICE_ID = 'tao/CreateRoleService';

    /** @var \tao_models_classes_RoleService */
    private $roleService;

    public function __construct(array $options = [])
    {
        $this->roleService = \tao_models_classes_RoleService::singleton();
        parent::__construct($options);
    }

    public function addRole(RoleContractInterface $role): ?\core_kernel_classes_Resource
    {
        return $this->roleService->addRole($role->getLabel(), $role->getIncludeRoles(), $role->getClass());
    }
}