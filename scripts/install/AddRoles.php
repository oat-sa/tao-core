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
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\scripts\install;

use oat\generis\model\GenerisRdf;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\reporting\Report;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\resources\TreeResourceLookup;
use oat\tao\model\role\contract\RoleContract;
use oat\tao\model\role\AddRoleService;
use oat\tao\model\role\AddRoleServiceInterface;
use oat\tao\model\role\RoleAclMapper;
use oat\tao\model\role\RoleAclMapperInterface;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\update\OntologyUpdater;

class AddRoles extends InstallAction
{

    public function __invoke($params = [])
    {
        //TODO: register with config
        $this->getServiceManager()->register(AddRoleService::SERVICE_ID, new AddRoleService());
        $this->getServiceManager()->register(RoleAclMapper::SERVICE_ID, new RoleAclMapper());

        /** @var AddRoleServiceInterface $createRoleService */
        $createRoleService = $this->getServiceManager()->get(AddRoleService::SERVICE_ID);

        /** @var RoleAclMapperInterface $createRoleService */
        $roleAclMapper = $this->getServiceManager()->get(RoleAclMapper::SERVICE_ID);


//
//
//        OntologyUpdater::syncModels();
//
//        $updatedRoles = [];
//        foreach ($this->getRulesForRole() as $role => $rules) {
//            foreach ($rules as $rule) {
//                AclProxy::applyRule($this->createAclRulesForRole($role, $rule));
//            }
//            preg_match("/.+#(\w+)Role$/", $role, $roleDefinition);
//            $updatedRoles[] = $roleDefinition[1] ?? $role;
//        }
//
//        return Report::createSuccess(
//            sprintf('Atomic roles "%s" successfully updated with rules', implode(', ',$updatedRoles))
//        );
    }

    private function getRulesForRole(): array
    {
        return [
            TaoRoles::ITEM_CLASS_NAVIGATOR => [
                ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'viewClassLabel'],
                ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'getOntologyData'],
                ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'index'],
            ],
            TaoRoles::ITEM_CLASS_EDITOR => [
                ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'editClassLabel'],
            ],
            TaoRoles::ITEM_CLASS_CREATOR => [
                ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'addSubClass'],
            ],
        ];
    }

    private function createAclRulesForRole(string $role, array $rule): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            $role,
            $rule
        );
    }
}
