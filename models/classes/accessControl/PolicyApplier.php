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

use Exception;
use oat\oatbox\reporting\Report;
use oat\oatbox\service\ConfigurableService;
use oat\tao\scripts\tools\accessControl\SetRolesAccess;

class PolicyApplier extends ConfigurableService
{
    public const SERVICE_ID = 'tao/PolicyApplier';
    public const OPTIONS_POLICIES = 'policies';

    public function applyPolicies(array $policyPaths): Report
    {
        /**
         * 1) Single place to store all permissions
         * 2) No need to apply on update/install separately
         *    checksum
         *    version
         *    ....
         * 3) Needs to be fast
         *
         * Formats? php, json,
         */

        $report = Report::createSuccess('Applying polices...');

        $configPolicies = $this->getPolicies(); //@TODO Probably we should store it on other place than filesystem (DB?)
        $appliedPolicies = [];

        $newPolicies = $this->getNewPolicies($policyPaths);

        /**
         * Get policies that were removed
         */
        $configPoliciesIds = array_keys($configPolicies);
        $newPoliciesIds = array_keys($newPolicies);
        $removedPolicies = array_diff($configPoliciesIds, $newPoliciesIds);

        foreach ($removedPolicies as $removedPolicyId) {
            /**
             * FIXME @TODO Remove delete permissions
             */
            $configPolicies[$removedPolicyId];
        }

        foreach ($newPolicies as $policy) {
            $policyId = $policy['id'];
            $checksum = crc32(json_encode($policy));

            if ($this->isPolicyUpdated($configPolicies, $policyId, $checksum)) {
                /**
                 * FIXME @TODO Apply the difference only
                 */
                $this->applyPermissions(
                    $policy['permissions']['action'],
                    $policy['permissions']['route']
                );
            }

            $appliedPolicies[$policyId] = [
                'checksum' => $checksum,
                'currentState' => $policy,
            ];

            $report->add(
                Report::createSuccess(
                    'Added policies: ' . var_export($policy['id'], true)
                )
            );
        }

        $this->setOption(self::OPTIONS_POLICIES, $appliedPolicies);

        $this->getServiceManager()->register(self::SERVICE_ID, $this);

        return $report;
    }

    private function getNewPolicies(array $policyPaths): array
    {
        $policies = [];

        foreach ($policyPaths as $policyPath) {
            foreach (glob($policyPath . '/*.php') as $policy) {
                $policyFile = realpath($policy);

                if (!is_readable($policyFile)) {
                    throw new Exception(
                        sprintf(
                            'Policy file "%s" is not readable',
                            $policyFile
                        )
                    );
                }

                /** @var array $policiesContent */
                $policiesContent = require $policyFile;

                foreach ($policiesContent as $policy) {
                    $policies[$policy['id']] = $policy;
                }
            }
        }

        return $policies;
    }

    private function isPolicyUpdated(array $existingPolicies, string $policyId, int $checksum): bool
    {
        $existingPolicy = $existingPolicies[$policyId] ?? null;

        if ($existingPolicy) {
            return $checksum === $existingPolicy['checksum'];
        }

        return true;
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

    private function getPolicies(): array
    {
        return $this->getOption(self::OPTIONS_POLICIES, []);
    }

    private function getSetRolesAccess(): SetRolesAccess
    {
        return $this->propagate(new SetRolesAccess());
    }
}
