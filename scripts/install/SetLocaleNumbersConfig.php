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
 * Copyright (c) 2016-2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use common_ext_ExtensionsManager as ExtensionsManager;
use common_report_Report as Report;
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\helpers\dateFormatter\EuropeanFormatter;
use tao_helpers_Date as DateHelper;

/**
 * Registers dataTimeFormatter and dateTime format.
 */
class SetLocaleNumbersConfig extends InstallAction
{
    /**
     * @inheritdoc
     *
     * Registers dataTimeFormatter and dateTime format.
     *
     * @param mixed $params
     *
     * @return Report
     */
    public function __invoke($params)
    {
        $ext = $this->getServiceManager()->get(ExtensionsManager::SERVICE_ID)->getExtensionById('tao');
        $ext->setConfig(DateHelper::CONFIG_KEY, new EuropeanFormatter());

        ClientLibConfigRegistry::getRegistry()->register(
            'util/locale',
            [
                'decimalSeparator' => '.',
                'thousandsSeparator' => '',
            ]
        );

        return new Report(Report::TYPE_SUCCESS, 'Tao Date format updated');
    }
}
