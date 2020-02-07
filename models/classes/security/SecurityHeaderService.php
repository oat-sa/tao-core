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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Service that can be used to protect actions.
 */
class SecurityHeaderService extends ConfigurableService implements MiddlewareInterface
{

    const SERVICE_ID = 'tao/SecurityHeader';

    const OPTION_WHITELIST = 'frameSourceWhitelist';

    public function addSecurityHeader(ResponseInterface $response)
    {
        $headers = $response->getHeader('Content-Type');
        if (empty($headers)) {
            \common_Logger::i('   => NO HEADERS');
        } else 
        foreach ($headers as $header) {
            \common_Logger::i('   => '.$header);
        }
        $securedResponse = $response->withHeader('Content-Security-Policy', $this->getFrameAncestorsHeader());
        return $securedResponse;
    }

    /**
     * Set the header that defines which sources are allowed to embed the pages.
     *
     * @return string
     */
    public function getFrameAncestorsHeader()
    {
        /** @var SettingsStorage $settingsStorage */
        $settingsStorage = $this->getServiceLocator()->get(SettingsStorage::SERVICE_ID);
        $whitelistedSources = $settingsStorage->get(CspHeaderSettingsInterface::CSP_HEADER_SETTING);

        if ($whitelistedSources === null) {
            $whitelistedSources = $this->hasOption(self::OPTION_WHITELIST)
                ? $this->getOption(self::OPTION_WHITELIST)
                : ["'none'"];
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

        return 'frame-ancestors '.implode(' ', $whitelistedSources);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $headers = $request->getHeader('X-Requested-With');
        if (empty($headers) || !in_array('XMLHttpRequest', $headers)) {
            // not a XHR request
            $response = $response->withHeader('Content-Security-Policy', $this->getFrameAncestorsHeader());
        }
        return $response;
    }

}
