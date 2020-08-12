<?php declare(strict_types=1);
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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\test\unit\models\classes\accessControl\func;

use oat\generis\test\TestCase;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclModel;

class AclModelTest extends TestCase
{
    public function testAclModel(): void
    {
        $model = new AclModel();
        $model->applyRule($this->mockAccessRule('role0', AccessRule::SCOPE_EXTENSION, ['ext' => 'ext1']));
        $model->applyRule($this->mockAccessRule('role1', AccessRule::SCOPE_CONTROLLER, ['ctrl' => 'sample1']));
        $model->applyRule($this->mockAccessRule('role1', AccessRule::SCOPE_CONTROLLER, ['ctrl' => 'sample2']));
        $model->applyRule($this->mockAccessRule('role2', AccessRule::SCOPE_ACTION, ['ctrl' => 'sample1', 'act' => 'action1']));
        $model->applyRule($this->mockAccessRule('role3', AccessRule::SCOPE_ACTION, ['ctrl' => 'sample1', 'act' => 'action2']));

        $controller = $model->getControllerAcl('sample1', 'ext1');
        $this->assertEquals(['role0', 'role1', 'role2'], $controller->getAllowedRoles('action1'));
        $this->assertEquals(['role0', 'role1', 'role3'], $controller->getAllowedRoles('action2'));
        $this->assertEquals(['role0', 'role1'], $controller->getAllowedRoles('action3'));

        $controller2 = $model->getControllerAcl('sample2', 'ext2');
        $this->assertEquals(['role1'], $controller2->getAllowedRoles('action1'));

        $controller3 = $model->getControllerAcl('sample3', 'ext3');
        $this->assertEquals([], $controller3->getAllowedRoles('action1'));
    }

    private function mockAccessRule(string $role, string $scope, array $data): AccessRule
    {
        $prophet = $this->prophesize(AccessRule::class);
        $prophet->isGrant()->willReturn(true);
        $prophet->getRoleId()->willReturn($role);
        $prophet->getScope()->willReturn($scope);
        if (isset($data['act'])) {
            $prophet->getAction()->willReturn($data['act']);
        }
        if (isset($data['ctrl'])) {
            $prophet->getController()->willReturn($data['ctrl']);
        }
        if (isset($data['ext'])) {
            $prophet->getExtensionId()->willReturn($data['ext']);
        }
        return $prophet->reveal();
    }
}
