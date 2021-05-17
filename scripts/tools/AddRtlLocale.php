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

namespace oat\tao\scripts\tools;

use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\tao\model\textDirection\RightToLeftTextDirectionRegistry;

class AddRtlLocale extends ScriptAction
{
    public const RTL_LOCALE = 'rtlLocale';

    protected function provideOptions()
    {
        return [
            self::RTL_LOCALE => [
                'prefix' => 'rtl',
                'longPrefix' => 'rtlLocale',
                'cast' => 'string',
                'flag' => false,
                'required' => true,
                'description' => 'add locales code to enable rtl handling',
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'This command allow to add rtl handling for locales';
    }

    /**
     * @inheritDoc
     */
    protected function run()
    {
        $report = Report::createInfo(sprintf('Registering new right to left locale: %s', $this->getOption(self::RTL_LOCALE)));

        $this->getRegistry()->addRtlLocale(
            $this->getOption(self::RTL_LOCALE)
        );

        $report->add(
            Report::createSuccess(
                sprintf(
                    'Registering new right to left locale "%s" was successful',
                    $this->getOption(self::RTL_LOCALE)
                )
            )
        );

        return $report;
    }

    private function getRegistry(): RightToLeftTextDirectionRegistry
    {
        return $this->getServiceManager()->get(RightToLeftTextDirectionRegistry::class);
    }
}
