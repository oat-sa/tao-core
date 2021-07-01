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

use Throwable;
use oat\oatbox\reporting\Report;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\ActionAccessControl;

/**
 * @example php index.php 'oat\tao\scripts\tools\accessControl\SetRolesAccess' [[ --revoke ]] --config [config.json|json_string]
 */
class SetRolesAccess extends ScriptAction
{
    public const OPTION_REVOKE = 'revoke';
    public const OPTION_CONFIG = 'config';

    public const CONFIG_RULES = 'rules';
    public const CONFIG_PERMISSIONS = 'permissions';

    /** @var Report */
    private $report;

    /** @var bool */
    private $revoke;

    /** @var array */
    private $rules;

    /** @var array */
    private $permissions;

    /**
     * @example revoke
     * [
     *     '--revoke',
     * ]
     *
     * @example config
     * [
     *     '--config', [
     *         'rules' => [
     *             'role1' => [
     *                 [
     *                     'ext' => 'extension',
     *                     'mod' => 'controller',
     *                     'act' => 'action',
     *                 ],
     *             ],
     *             'role2' => [
     *                 [
     *                     'ext' => 'extension',
     *                     'mod' => 'controller',
     *                     'act' => 'action',
     *                 ],
     *             ],
     *         ],
     *         'permissions' => [
     *             controller' => [
     *                 'action' => [
     *                     'role1' => 'READ',
     *                     'role2' => 'WRITE',
     *                 ],
     *             ],
     *         ],
     *     ],
     * ]
     * [
     *     '--config', 'json_string'
     * ]
     * [
     *     '--config', 'path_to_json_file'
     * ]
     */
    protected function provideOptions()
    {
        return [
            self::OPTION_REVOKE => [
                'prefix' => 'r',
                'longPrefix' => self::OPTION_REVOKE,
                'flag' => true,
                'defaultValue' => false,
                'description' => 'Revoke (remove) provided permissions/rules from roles.',
            ],
            self::OPTION_CONFIG => [
                'longPrefix' => self::OPTION_CONFIG,
                'required' => true,
                'description' => 'List of rules and/or permissions to apply (add)/revoke (remove).',
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'Allow to add/remove list of permissions/rules to a specific action and role.';
    }

    protected function run()
    {
        $this->report = Report::createInfo('Role rules and permissions changes have started.');
        $this->parseOptions();

        $serviceManager = $this->getServiceManager();

        /** @var ActionAccessControl $actionAccessControl */
        $actionAccessControl = $serviceManager->get(ActionAccessControl::SERVICE_ID);

        if ($this->revoke === false) {
            $actionAccessControl->addPermissions($this->permissions);
            $this->applyRules();
        } else {
            $actionAccessControl->removePermissions($this->permissions);
            $this->revokeRules();
        }

        $serviceManager->register(ActionAccessControl::SERVICE_ID, $actionAccessControl);
    }

    private function parseOptions(): void
    {
        $this->revoke = $this->hasOption(self::OPTION_REVOKE);

        $config = $this->parseArrayOption(self::OPTION_CONFIG);
        $this->rules = $config[self::CONFIG_RULES] ?? [];
        $this->permissions = $config[self::CONFIG_PERMISSIONS] ?? [];
    }

    private function parseArrayOption(string $option): array
    {
        $value = $this->getOption($option);

        try {
            if (is_string($value)) {
                if (is_file($value) && pathinfo($value, PATHINFO_EXTENSION) === 'json') {
                    $value = file_get_contents($value);
                }

                $value = json_decode($value, true);
            }
        } catch (Throwable $exception) {
            $this->report->add(Report::createWarning($exception->getMessage()));
            $value = [];
        }

        return (array) $value;
    }

    private function applyRules(): void
    {
        foreach ($this->rules as $role => $masks) {
            foreach ($masks as $mask) {
                AclProxy::applyRule($this->createAccessRule($role, $mask));
            }
        }
    }

    private function revokeRules(): void
    {
        foreach ($this->rules as $role => $masks) {
            foreach ($masks as $mask) {
                AclProxy::revokeRule($this->createAccessRule($role, $mask));
            }
        }
    }

    /**
     * @param string|array $mask
     */
    private function createAccessRule(string $role, $mask): AccessRule
    {
        return new AccessRule(AccessRule::GRANT, $role, $mask);
    }
}
