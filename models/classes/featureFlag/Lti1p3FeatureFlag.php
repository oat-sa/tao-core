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
 */

declare(strict_types=1);

namespace oat\tao\model\featureFlag;

use oat\oatbox\service\ConfigurableService;

class Lti1p3FeatureFlag extends ConfigurableService
{
    public const SERVICE_ID = 'tao/FeatureFlag';

    public const OPTION_DISABLED_SECTIONS = 'optionDisabledSections';
    public const OPTION_LTI_1P3_ENABLED = 'optionLti1p3Enabled';

    public function isSectionDisabled(string $section): bool
    {
        return in_array($section, $this->getOption(self::OPTION_DISABLED_SECTIONS), true);
    }

    public function isLti1p3Enabled(): bool
    {
        return $this->getOption(self::OPTION_LTI_1P3_ENABLED);
    }
}
