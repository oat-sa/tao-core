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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\tao\model\service\SettingsStorage;
use oat\tao\model\settings\CspHeaderSettingsInterface;

/**
 * Set the default CSP Header value during install
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class SetDefaultCSPHeader extends InstallAction
{

    /**
     * @inheritdoc
     */
    public function __invoke($params)
    {
        /** @var SettingsStorage $settingsStorage */
        $settingsStorage = $this->getServiceLocator()->get(SettingsStorage::SERVICE_ID);
        $settingsStorage->set(CspHeaderSettingsInterface::CSP_HEADER_SETTING, 'self');
    }
}
