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

namespace oat\tao\helpers\form\Specification;

use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use tao_helpers_form_elements_Combobox;

class DependencyPropertyWidgetSpecification
{
    public const DEPENDENT_SINGLE_RESTRICTED_TYPES = [
        tao_helpers_form_elements_Combobox::WIDGET_ID,
        SearchDropdown::WIDGET_ID
    ];

    public function isSatisfiedBy(
        string $targetWidgetUri,
        string $selectedWidgetUri,
        string $previewsWidget = null
    ): bool {
        if ($previewsWidget === null) {
            if (in_array($selectedWidgetUri, self::DEPENDENT_SINGLE_RESTRICTED_TYPES, true)) {
                return in_array($targetWidgetUri, self::DEPENDENT_SINGLE_RESTRICTED_TYPES, true);
            }

            if ($selectedWidgetUri === SearchTextBox::WIDGET_ID) {
                return $targetWidgetUri === SearchTextBox::WIDGET_ID;
            }

            return false;
        }

        if ($previewsWidget === $targetWidgetUri) {
            return true;
        }

        if (in_array($previewsWidget, self::DEPENDENT_SINGLE_RESTRICTED_TYPES, true)) {
            return in_array($targetWidgetUri, self::DEPENDENT_SINGLE_RESTRICTED_TYPES, true);
        }

        if ($previewsWidget === SearchTextBox::WIDGET_ID) {
            return $targetWidgetUri === SearchTextBox::WIDGET_ID;
        }

        return false;
    }
}
