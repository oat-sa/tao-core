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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\featureFlag;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepositoryInterface;

class FeatureFlagChecker extends ConfigurableService implements FeatureFlagCheckerInterface
{
    public function isEnabled(string $feature): bool
    {
        if (array_key_exists($feature, $_ENV)) {
            return filter_var($_ENV[$feature], FILTER_VALIDATE_BOOLEAN) ?? false;
        }

        return $this->getFeatureFlagRepository()->get($feature);
    }

    private function getFeatureFlagRepository(): FeatureFlagRepositoryInterface
    {
        return $this->getServiceManager()->get(FeatureFlagRepositoryInterface::class);
    }
}
