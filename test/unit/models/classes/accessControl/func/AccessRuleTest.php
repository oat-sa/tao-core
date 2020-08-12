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

class AccessRuleTest extends TestCase
{
    /**
     * @param string $actionId
     * @param array $options
     * @param bool $expected
     *
     * @dataProvider dataProviderMask
     */
    public function testMask($mask, array $expected = []): void
    {
        if (empty($expected)) {
            $this->expectException(\common_exception_InconsistentData::class);
        }
        $accessRule = new AccessRule(AccessRule::GRANT, 'fakeRole', $mask);
        $this->assertEquals($expected['scope'], $accessRule->getScope());
        $this->assertEquals($expected['ext'] ?? null, $accessRule->getExtensionId());
        $this->assertEquals($expected['ctrl'] ?? null, $accessRule->getController());
        $this->assertEquals($expected['act'] ?? null, $accessRule->getAction());
    }
    
    public function dataProviderMask(): array
    {
        return [
            'invalid string mask' => [
                'mask' => 'NotAmasK'
            ],
            'valid controller string mask' => [
                'mask' => \tao_actions_Main::class,
                'expected' => [
                    'scope' => AccessRule::SCOPE_CONTROLLER,
                    'ctrl' => \tao_actions_Main::class
                ],
            ],
            'valid action string mask' => [
                'mask' => \tao_actions_Main::class.'@index',
                'expected' => [
                    'scope' => AccessRule::SCOPE_ACTION,
                    'ctrl' => \tao_actions_Main::class,
                    'act' => 'index'
                ],
            ],
            'invalid array mask' => [
                'mask' => ['a' => 'b'],
            ],
            'valid extension array mask' => [
                'mask' => [
                    'ext' => 'tao'
                ],
                'expected' => [
                    'scope' => AccessRule::SCOPE_EXTENSION,
                    'ext' => 'tao'
                ],
            ],
            'valid controller string mask' => [
                'mask' => \tao_actions_Main::class,
                'expected' => [
                    'scope' => AccessRule::SCOPE_CONTROLLER,
                    'ctrl' => \tao_actions_Main::class
                ],
            ],
            'valid action string mask' => [
                'mask' => \tao_actions_Main::class.'@index',
                'expected' => [
                    'scope' => AccessRule::SCOPE_ACTION,
                    'ctrl' => \tao_actions_Main::class,
                    'act' => 'index'
                ],
            ],
        ];
    }
}
