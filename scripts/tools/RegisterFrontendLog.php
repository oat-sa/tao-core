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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\scripts\tools;

use common_report_Report as Report;
use common_Exception;
use oat\oatbox\extension\AbstractAction;

/**
 * Class RegisterFrontendLog
 * @package oat\tao\scripts\tools
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class RegisterFrontendLog extends AbstractAction
{

    /**
     * @param array $params
     * @return Report
     * @throws common_Exception
     */
    public function __invoke($params)
    {
        $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)
            ->getExtensionById('tao');

        $config = $extension->getConfig('client_lib_config_registry');
        $config[ 'core/logger'] = [
            'level' => 'warn',
            'loggers' => [
                'core/logger/console' => [
                    'level' => 'warn'
                ],
                'core/logger/http' => [
                    'level' => 'error'
                ],
            ]
        ];
        $extension->setConfig('client_lib_config_registry', $config);

        return new Report(
            Report::TYPE_INFO,
            'Http implementation of frontend log has been registered'
        );
    }
}
