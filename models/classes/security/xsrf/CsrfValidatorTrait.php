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

namespace oat\tao\model\security\xsrf;

use common_Exception;
use common_exception_Error;
use common_exception_Unauthorized;
use common_session_SessionManager;
use oat\oatbox\service\ServiceManager;

/**
 * Trait that can be used to validate a CSRF token (using the X-CSRF-Token header)
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
trait CsrfValidatorTrait
{

    /**
     * @var string[]
     */
    private $requestHeaders;

    /**
     * @var string
     */
    private $csrfTokenHeader = 'X-CSRF-Token';

    /**
     * Validate the current request using the CSRF token header.
     *
     * @return string
     * @throws common_exception_Error
     * @throws common_exception_Unauthorized
     * @throws common_Exception
     */
    public function validateCsrf()
    {
        $csrfToken = $this->getTokenHeader();
        if (!$csrfToken) {
            $this->logCsrfFailure('Missing X-CSRF-Token header.');
        }

        /** @var TokenService $tokenService */
        $tokenService = ServiceManager::getServiceManager()->get(TokenService::SERVICE_ID);
        $newToken = null;

        try {
            $newToken = $tokenService->validateToken($csrfToken);
        } catch (common_exception_Unauthorized $e) {
            $this->logCsrfFailure($e->getMessage(), $csrfToken);
        }

        $this->setTokenHeader($newToken);

        return $newToken;
    }

    /**
     * Set the X-CSRF-Token header
     * @param string $token
     */
    public function setTokenHeader($token)
    {
        header($this->csrfTokenHeader . ': ' . $token);
    }

    /**
     * @param string $exceptionMessage
     * @param string|null $token
     * @throws common_exception_Unauthorized
     * @throws common_exception_Error
     */
    abstract public function logCsrfFailure($exceptionMessage, $token = null);

    /**
     * Retrieve the token header.
     *
     * @return string|null
     */
    private function getTokenHeader()
    {
        $tokenHeaderValue = null;
        $headers = $this->getRequestHeaders();
        $headerKey = strtolower($this->csrfTokenHeader);

        return isset($headers[$headerKey]) ? $headers[$headerKey] : null;
    }

    /**
     * Retrieve the token header.
     *
     * @return string|null
     */
    private function getRequestHeaders()
    {
        if ($this->requestHeaders !== null) {
            $this->requestHeaders;
        }

        if (function_exists('apache_request_headers')) {
            foreach (apache_request_headers() as $key => $value) {
                $this->requestHeaders[strtolower($key)] = $value;
            }
        } else {
            foreach ($_SERVER as $name => $value) {
                if (strpos($name, 'HTTP_') === 0) {
                    $this->requestHeaders[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
                }
            }
        }

        return $this->requestHeaders;
    }
}
