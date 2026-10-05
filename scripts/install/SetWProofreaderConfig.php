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
 *
 * Params (optional):
 * - enabled: bool
 * - serviceId: string (WebSpellChecker license / service id)
 * - srcUrl: string (WSC bundle URL; use on-prem host when applicable)
 * - lang: string (e.g. auto, en_US)
 * - autoSearch: bool (default true; vendor focus-polling + instance backstop,
 *   contained to editor editables via enableAutoSearchIn)
 * - ignoreClasses, ignoreElements, ignoreAttributes, disableAutoSearchIn: optional arrays merged
 *   with TAO defaults (math-tex, widget-box, cke_widget_*, pre/code, .cke_source, etc.).
 *   NOTE: QTI structural attributes (data-qti-class, data-widget, data-serial)
 *   must stay OUT of ignoreAttributes: they sit on prose containers (choice
 *   prompt/option editables carry data-serial) and WSC prunes the whole
 *   subtree of elements carrying an ignored attribute.
 * - enableAutoSearchIn: optional array merged with TAO defaults
 *   (.cke_wysiwyg_frame, .cke_editable); allowlist, fail-closed
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

        foreach (['ignoreClasses', 'ignoreElements', 'ignoreAttributes', 'disableAutoSearchIn', 'enableAutoSearchIn'] as $listKey) {
            if (!empty($params[$listKey]) && is_array($params[$listKey])) {
                $config[$listKey] = $params[$listKey];
            }
        }

        ClientLibConfigRegistry::getRegistry()->register('tao/ckeditor/wproofreaderBootstrap', $config);

        return Report::createSuccess('WProofreader client configuration registered.');
    }
}
