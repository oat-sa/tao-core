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

/**
 * Registers WProofreader client settings for backoffice CKEditor 4.
 */
class SetWProofreaderConfig extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $config = [
            'enabled' => filter_var($params['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'serviceId' => (string)($params['serviceId'] ?? ''),
            'srcUrl' => (string)(
                $params['srcUrl']
                ?? 'https://svc.webspellchecker.net/spellcheck31/wscbundle/wscbundle.js'
            ),
            'lang' => (string)($params['lang'] ?? 'auto'),
            'autoSearch' => filter_var($params['autoSearch'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];

        $listKeys = [
            'ignoreClasses',
            'ignoreElements',
            'ignoreAttributes',
            'disableAutoSearchIn',
            'enableAutoSearchIn'
        ];
        foreach ($listKeys as $listKey) {
            if (!empty($params[$listKey]) && is_array($params[$listKey])) {
                $config[$listKey] = $params[$listKey];
            }
        }

        ClientLibConfigRegistry::getRegistry()->register('tao/ckeditor/wproofreaderBootstrap', $config);

        return Report::createSuccess('WProofreader client configuration registered.');
    }
}
