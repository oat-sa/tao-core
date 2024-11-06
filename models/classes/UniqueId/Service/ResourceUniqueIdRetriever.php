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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\UniqueId\Service;

use core_kernel_classes_Resource;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\TaoOntology;

class ResourceUniqueIdRetriever
{
    private FeatureFlagCheckerInterface $featureFlagChecker;

    public function __construct(FeatureFlagCheckerInterface $featureFlagChecker)
    {
        $this->featureFlagChecker = $featureFlagChecker;
    }

    public function retrieve(core_kernel_classes_Resource $resource): ?string
    {
        if (!$this->featureFlagChecker->isEnabled('FEATURE_FLAG_UNIQUE_NUMERIC_QTI_IDENTIFIER')) {
            return null;
        }

        $property = $resource->getProperty(TaoOntology::PROPERTY_UNIQUE_IDENTIFIER);
        $uniqueId = $resource->getOnePropertyValue($property);

        return !empty($uniqueId) ? $uniqueId->literal : null;
    }
}
