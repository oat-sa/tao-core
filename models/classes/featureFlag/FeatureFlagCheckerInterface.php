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

interface FeatureFlagCheckerInterface
{
    public const FEATURE_FLAG_ADVANCED_SEARCH_DISABLED = 'FEATURE_FLAG_ADVANCED_SEARCH_DISABLED';
    public const FEATURE_FLAG_TABULAR_IMPORT = 'FEATURE_FLAG_TABULAR_IMPORT_ENABLED';
    public const FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED = 'FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED';

    public function isEnabled(string $feature): bool;
}
