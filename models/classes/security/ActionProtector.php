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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\security;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\service\SettingsStorage;
use oat\tao\model\settings\CspHeaderSettingsInterface;

/**
 * Service that can be used to protect actions.
 *
 * @author Martijn Swinkels <martijn@taotesting.com>
 */
class ActionProtector extends ConfigurableService
{
    public const SERVICE_ID = 'tao/actionProtection';

    /**
     * Set the header that defines which sources are allowed to embed the pages.
     *
     * @return void
     */
    public function setFrameAncestorsHeader()
    {
        /** @var SettingsStorage $settingsStorage */
        $settingsStorage = $this->getServiceLocator()->get(SettingsStorage::SERVICE_ID);
        $whitelistedSources = $settingsStorage->get(CspHeaderSettingsInterface::CSP_HEADER_SETTING);

        if ($whitelistedSources === null) {
            $whitelistedSources = ["'none'"];
        }

        // Wrap directives in quotes
        if (in_array($whitelistedSources, ['self', 'none'])) {
            $whitelistedSources = ["'" . $whitelistedSources . "'"];
        }

        if ($whitelistedSources === 'list') {
            $whitelistedSources = json_decode($settingsStorage->get(CspHeaderSettingsInterface::CSP_HEADER_LIST), true);
        }

        if (!is_array($whitelistedSources)) {
            $whitelistedSources = [$whitelistedSources];
        }

        header(sprintf(
            'Content-Security-Policy: frame-ancestors %s',
            implode(' ', $whitelistedSources)
        ));
    }
}
