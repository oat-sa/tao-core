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
use oat\tao\model\textDirection\RightToLeftTextDirectionRegistry;

class AddRtlLocales extends ScriptAction
{
    public const RTL_LOCALES = 'rtlLocales';

    protected function provideOptions()
    {
        return [
            self::RTL_LOCALES => [
                'prefix'      => 'rtl',
                'longPrefix'  => 'rtlLocales',
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
        $this->getRegistry()->addRtlLocales(
            $this->getOption(self::RTL_LOCALES)
        );
    }

    private function getRegistry(): RightToLeftTextDirectionRegistry
    {
        return $this->getServiceManager()->get(RightToLeftTextDirectionRegistry::class);
    }
}
