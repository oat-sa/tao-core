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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\FeatureFlag;

use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\tao\model\featureFlag\FeatureFlagChecker;

/**
 * Usage to save a feature flag:
 *  php index.php 'oat\tao\scripts\tools\FeatureFlag\FeatureFlagTool' -s FEATURE_FLAG_EXAMPLE -v true
 *
 * Usage to get feature flag report:
 *  php index.php 'oat\tao\scripts\tools\FeatureFlag\FeatureFlagTool' -r true
 *
 * Usage to get info about feature flag:
 *  php index.php 'oat\tao\scripts\tools\FeatureFlag\FeatureFlagTool' -i FEATURE_FLAG_EXAMPLE
 *
 * Clear feature flag cache:
 *  php index.php 'oat\tao\scripts\tools\FeatureFlag\FeatureFlagTool' -cc true
 */
class FeatureFlagTool extends ScriptAction
{
    private const OPTION_SAVE = 'save';
    private const OPTION_VALUE = 'value';
    private const OPTION_INFO = 'store';
    private const OPTION_REPORT = 'report';
    private const OPTION_CLEAR_CACHE = 'clear-cache';

    protected function provideOptions(): array
    {
        return [
            self::OPTION_SAVE => [
                'prefix'      => 's',
                'longPrefix'  => self::OPTION_SAVE,
                'description' => 'Save a feature flag',
            ],
            self::OPTION_VALUE => [
                'prefix'      => 'v',
                'longPrefix'  => self::OPTION_VALUE,
                'description' => 'Boolean value to be saved. I.e: 0 or 1',
            ],
            self::OPTION_INFO => [
                'prefix'      => 'i',
                'longPrefix'  => self::OPTION_INFO,
                'description' => 'Get information about the feature flag value',
            ],
            self::OPTION_REPORT => [
                'prefix'      => 'r',
                'longPrefix'  => self::OPTION_REPORT,
                'description' => 'List all feature flags and their values',
            ],
            self::OPTION_CLEAR_CACHE => [
                'prefix'      => 'cc',
                'longPrefix'  => self::OPTION_CLEAR_CACHE,
                'description' => 'Clear all cache for DB feature flags',
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'Set a client-side CSRF token pool store preference.';
    }

    protected function run(): Report
    {
        if ($featureFlagToSave = $this->getOption(self::OPTION_SAVE)) {
            if (!$this->hasOption(self::OPTION_VALUE)) {
                return Report::createError(
                    sprintf('Option %s is mandatory', self::OPTION_VALUE)
                );
            }

            $value = filter_var($this->getOption(self::OPTION_VALUE), FILTER_VALIDATE_BOOLEAN) ?? false;

            $this->getFeatureFlagChecker()->save($featureFlagToSave, $value);

            return Report::createSuccess(
                sprintf('Feature %s saved as %s', $featureFlagToSave, var_export($value, true))
            );
        }

        if ($featureFlagToGetInfo = $this->getOption(self::OPTION_INFO)) {
            $info = $this->getFeatureFlagChecker()->list()[$featureFlagToGetInfo] ?? null;

            return Report::createSuccess(sprintf('%s:%s', $featureFlagToGetInfo, var_export($info, true)));
        }

        if ($this->getOption(self::OPTION_CLEAR_CACHE)) {
            return Report::createSuccess(
                sprintf(
                    'Total of %s FeatureFlags cleared from cache',
                    $this->getFeatureFlagChecker()->clearCache()
                )
            );
        }

        if ($this->getOption(self::OPTION_REPORT)) {
            $report =  Report::createSuccess('Feature list');

            foreach ($this->getFeatureFlagChecker()->list() as $feature => $value) {
                $report->add(Report::createInfo(sprintf('%s:%s', $feature, var_export($value, true))));
            }

            return $report;
        }

        return Report::createError('No option selected');
    }

    private function getFeatureFlagChecker(): FeatureFlagChecker
    {
        return $this->getServiceManager()->getContainer()->get(FeatureFlagChecker::class);
    }
}
