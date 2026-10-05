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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\scripts\install;

use oat\oatbox\reporting\Report;
use oat\tao\model\ClientLibConfigRegistry;

/**
 * Registers native SCAYT CKEditor settings for authoring.
 *
 * Params (optional):
 * - enabled: bool (default true)
 * - customerId: string (WebSpellChecker license id; empty = bundled demo id)
 * - sLang: string (default spellcheck language, e.g. en_US)
 * - elementsToIgnore: string (tag-name list, e.g. 'style,script,math,pre,code')
 * - minWordLength: int
 */
class SetScaytConfig extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $config = [
            'enabled' => filter_var($params['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'scayt_sLang' => (string)($params['sLang'] ?? 'en_US'),
            'scayt_elementsToIgnore' => (string)($params['elementsToIgnore'] ?? 'style,script,math,pre,code'),
            'scayt_minWordLength' => (int)($params['minWordLength'] ?? 3),
        ];

        if (!empty($params['customerId'])) {
            $config['scayt_customerId'] = (string)$params['customerId'];
        }

        ClientLibConfigRegistry::getRegistry()->register('tao/ckeditor/scaytBootstrap', $config);

        return Report::createSuccess('SCAYT client configuration registered.');
    }
}
