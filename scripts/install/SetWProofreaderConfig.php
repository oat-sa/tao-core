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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\scripts\install;

use oat\oatbox\reporting\Report;
use oat\tao\model\ClientLibConfigRegistry;

class SetWProofreaderConfig extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $config = [
            'serviceId' => (string)($params['serviceId'] ?? ''),
            'srcUrl' => (string)(
                $params['srcUrl']
                ?? 'https://svc.webspellchecker.net/spellcheck31/wscbundle/wscbundle.js'
            ),
            'lang' => (string)($params['lang'] ?? 'auto'),
            'autoSearch' => filter_var($params['autoSearch'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];

        $configId = 'tao/ckeditor/wproofreaderBootstrap';
        $registry = ClientLibConfigRegistry::getRegistry();
        $registeredConfig = $registry->isRegistered($configId) ? $registry->get($configId) : null;
        if ($registeredConfig !== null) {
            unset($registeredConfig['enabled']);
        }
        $listKeys = [
            'ignoreClasses',
            'ignoreElements',
            'ignoreAttributes',
            'disableAutoSearchIn',
            'enableAutoSearchIn'
        ];
        foreach ($listKeys as $listKey) {
            if (array_key_exists($listKey, $params) && is_array($params[$listKey])) {
                $config[$listKey] = $params[$listKey];
                if ($registeredConfig !== null) {
                    $registeredConfig[$listKey] = [];
                }
            }
        }

        if ($registeredConfig !== null) {
            $registry->set($configId, $registeredConfig);
        }
        $registry->register($configId, $config);

        return Report::createSuccess('WProofreader client configuration registered.');
    }
}
