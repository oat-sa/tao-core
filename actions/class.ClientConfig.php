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
 * Copyright (c) 2013-2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

use oat\tao\model\clientConfig\GetConfigQuery;
use oat\tao\model\clientConfig\ClientConfigStorage;

/**
 * Generates client side configuration.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
class tao_actions_ClientConfig extends tao_actions_CommonModule
{
    /**
     * Get the require.js' config file
     */
    public function config(): void
    {
        $this->setContentHeader('application/javascript');

        $params = $this->getPsrRequest()->getQueryParams();

        $getConfigQuery = new GetConfigQuery(
            $params['extension'] ?? Context::getInstance()->getExtensionName(),
            $params['action'] ?? Context::getInstance()->getActionName(),
            $params['module'] ?? Context::getInstance()->getModuleName(),
            $params['shownExtension'] ?? null,
            $params['shownStructure'] ?? null
        );
        $config = $this->getClientConfigStorage()->getConfig($getConfigQuery);

        foreach ($config as $key => $value) {
            $this->setData($key, $value);
        }

        $this->setView('client_config.tpl');
    }

    private function getClientConfigStorage(): ClientConfigStorage
    {
        return $this->getPsrContainer()->get(ClientConfigStorage::class);
    }
}
