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

namespace oat\tao\model\menu;

use LogicException;
use oat\oatbox\service\ConfigurableService;

class SectionVisibilityFilter extends ConfigurableService implements SectionVisibilityFilterInterface
{
    public const SERVICE_ID = 'tao/SectionVisibilityFilter';
    public const EXCLUDED_SECTION_LIST_PROVIDERS = 'ExcludedSectionListProvider';

    /**
     * @throws \Exception
     */
    public function isHidden(string $section): bool
    {
        return in_array($section, $this->getExcludedSections(), true);
    }

    /**
     * @throws \Exception
     */
    private function getExcludedSections(): array
    {
        $hiddenSections = [];
        foreach ($this->getOption(self::EXCLUDED_SECTION_LIST_PROVIDERS) as $excludedSectionListProvider) {
            if (!$excludedSectionListProvider instanceof ExcludedSectionListProviderInterface) {
                throw new LogicException('excluded section list_providers has to be instance of ExcludedSectionListInterface');
            }

            $this->propagate($excludedSectionListProvider);

            $hiddenSections = array_merge($hiddenSections, $excludedSectionListProvider->getExcludedSections());
        }

        return $hiddenSections;
    }
}
