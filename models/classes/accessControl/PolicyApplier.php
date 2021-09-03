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

namespace oat\tao\model\accessControl;

use oat\oatbox\reporting\Report;
use oat\oatbox\service\ConfigurableService;
use oat\tao\scripts\tools\accessControl\SetRolesAccess;

class PolicyApplier extends ConfigurableService
{
    public function applyPolicies(array $policyPaths): Report
    {
        $report = Report::createSuccess('Applying polices...');

        foreach ($policyPaths as $policyPath) {
            foreach (glob($policyPath . '/*.php') as $policy) {
                $policyFile = realpath($policy);

                $report->add(
                    Report::createInfo(
                        sprintf(
                            'Processing policy file %s',
                            $policyFile
                        )
                    )
                );

                if (!is_readable($policyFile)) {
                    $report->add(
                        Report::createError(
                            sprintf(
                                'Policy file %s not found',
                                $policyFile
                            )
                        )
                    );
                }

                $policies = require $policyFile;
            }
        }

        return $report;
    }

    private function applyPermissions(array $permissions, array $rules, $isRevoke = false): void
    {
        $options = [
            '--' . SetRolesAccess::OPTION_CONFIG,
            [
                SetRolesAccess::CONFIG_PERMISSIONS => $permissions,
                SetRolesAccess::CONFIG_RULES => $rules,
            ],
        ];

        if ($isRevoke) {
            array_unshift($options, '--' . SetRolesAccess::OPTION_REVOKE);
        }

        $setRolesAccess = $this->getSetRolesAccess();
        $setRolesAccess($options);
    }

    private function getSetRolesAccess(): SetRolesAccess
    {
        return $this->propagate(new SetRolesAccess());
    }
}
