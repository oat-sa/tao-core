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

/**
 * Trait that can be used to validate a CSRF token (using the X-CSRF-Token header)
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
trait CsrfValidatorTrait
{

    /**
     * @var string
     */
    private $CsrfTokenHeader = 'X-CSRF-Token';

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
        if (!$this->getHeader($this->CsrfTokenHeader)) {
            $this->logCsrfFailure('Missing X-CSRF-Token header.');
        }

        $newToken = null;
        $csrfToken = $this->getHeader($this->CsrfTokenHeader);

        /** @var TokenService $tokenService */
        $tokenService = $this->getServiceLocator()->get(TokenService::SERVICE_ID);

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
        header($this->CsrfTokenHeader . ': ' . $token);
    }

    /**
     * @param string $exceptionMessage
     * @param string|null $token
     * @throws common_exception_Unauthorized
     * @throws common_exception_Error
     */
    private function  logCsrfFailure($exceptionMessage, $token = null)
    {
        $userIdentifier = $this->getSession()->getUser()->getIdentifier();
        $requestMethod  = $this->getRequestMethod();
        $requestUri     = $this->getRequestURI();
        $requestHeaders = $this->getRequest()->getHeaders();

        $this->logWarning('Failed to validate CSRF token. The following exception occurred: ' . $exceptionMessage);
        $this->logWarning(
            "CSRF validation information: \n" .
            'Provided token: ' . ($token ?: 'none')  . " \n" .
            'User identifier: ' . $userIdentifier  . " \n" .
            'Request: [' . $requestMethod . '] ' . $requestUri   . " \n" .
            "Request Headers : \n" .
            urldecode(http_build_query($requestHeaders, '', "\n"))
        );

        throw new common_exception_Unauthorized($exceptionMessage);
    }
}
