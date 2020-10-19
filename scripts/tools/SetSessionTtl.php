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

namespace oat\tao\scripts\tools;

use common_Exception as Exception;
use common_report_Report as Report;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use common_session_php_KeyValueSessionHandler as KeyValueSessionHandler;

class SetSessionTtl extends ScriptAction
{
    /**
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'ttl' => [
                'prefix' => 't',
                'longPrefix' => 'ttl',
                'cast' => 'integer',
                'defaultValue' => 0,
                'description' => 'The number of seconds that indicate the duration of the session.',
            ],
        ];
    }

    /**
     * @return string
     */
    protected function provideDescription()
    {
        return 'Allow to set up the duration of the session.';
    }

    /**
     * @throws Exception
     * @throws InvalidServiceManagerException
     *
     * @return Report
     */
    protected function run()
    {
        $serviceKey = 'tao/session';
        $serviceManager = $this->getServiceManager();
        $sessionHandler = $serviceManager->get($serviceKey);

        if ($sessionHandler instanceof KeyValueSessionHandler) {
            $sessionHandler->setOption(
                KeyValueSessionHandler::OPTION_SESSION_TTL,
                $this->getOption('ttl') ?: null
            );

            $serviceManager->register($serviceKey, $sessionHandler);

            return Report::createSuccess('The session TTL value was successfully configured.');
        }

        return Report::createInfo('The session TTL value was not configured.');
    }
}
