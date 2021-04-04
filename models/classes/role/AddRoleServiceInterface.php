<?php

declare(strict_types=1);

namespace oat\tao\model\role;

use oat\tao\model\role\contract\RoleContractInterface;

interface AddRoleServiceInterface
{
    public function addRole(RoleContractInterface $role): ?\core_kernel_classes_Resource;
}