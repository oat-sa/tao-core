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

namespace oat\tao\scripts\tools\accessControl;

use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\accessControl\func\AccessRule;

class ApplyRules extends ScriptAction
{
    public const OPTION_REVOKE = 'revoke';
    public const OPTION_RULES = 'rules';

    /**
     * @example Apply rules [
     *     '--rules', [
     *         'role1' => [
     *             ['ext' => 'extension', 'mod' => 'controller', 'act' => 'action'],
     *         ],
     *         'role2' => [
     *             ['ext' => 'extension', 'mod' => 'controller', 'act' => 'action'],
     *         ],
     *     ],
     * ]
     *
     * @example Revoke rules [
     *     '--revoke'
     *     '--rules', [
     *         'role1' => [
     *             ['ext' => 'extension', 'mod' => 'controller', 'act' => 'action'],
     *         ],
     *         'role2' => [
     *             ['ext' => 'extension', 'mod' => 'controller', 'act' => 'action'],
     *         ],
     *     ],
     * ]
     */
    protected function provideOptions()
    {
        return [
            self::OPTION_REVOKE => [
                'longPrefix' => self::OPTION_REVOKE,
                'flag' => 'true',
                'cast' => 'boolean',
                'defaultValue' => false,
                'description' => 'Revoke provided rules.',
            ],
            self::OPTION_RULES => [
                'longPrefix' => self::OPTION_RULES,
                'cast' => 'array',
                'defaultValue' => [],
                'description' => 'List of rules to apply or revoke.',
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'Apply or revoke rules.';
    }

    protected function run()
    {
        $this->getOption(self::OPTION_REVOKE) === true
            ? $this->revokeRules()
            : $this->applyRules();
    }

    private function applyRules(): void
    {
        foreach ($this->getOption(self::OPTION_RULES) as $role => $masks) {
            foreach ($masks as $mask) {
                AclProxy::applyRule($this->createAclRulesForRole($role, $mask));
            }
        }
    }

    private function revokeRules(): void
    {
        foreach ($this->getOption(self::OPTION_RULES) as $role => $masks) {
            foreach ($masks as $mask) {
                AclProxy::revokeRule($this->createAclRulesForRole($role, $mask));
            }
        }
    }

    private function createAclRulesForRole(string $role, array $rule): AccessRule
    {
        return new AccessRule(AccessRule::GRANT, $role, $rule);
    }
}
