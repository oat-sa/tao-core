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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\helpers\form\elements\xhtml;

use common_session_SessionManager;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\tao\model\security\xsrf\TokenService;
use tao_helpers_form_elements_xhtml_Hidden;

/**
 * Class provides the CSRF token form element
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class CsrfToken extends tao_helpers_form_elements_xhtml_Hidden
{
    use LoggerAwareTrait;

    /**
     * @inheritdoc
     */
    public function render()
    {
        /** @var TokenService $tokenService */
        $tokenService = ServiceManager::getServiceManager()->get(TokenService::SERVICE_ID);
        $formToken = $tokenService->getFormToken();
        $this->setValue($formToken->getValue());

        return parent::render();
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        $csrfToken = $this->getEvaluatedValue();
        if (!$csrfToken) {
            $this->logCsrfFailure('No CSRF token provided in form');
            return false;
        }

        /** @var TokenService $tokenService */
        $tokenService = ServiceManager::getServiceManager()->get(TokenService::SERVICE_ID);

        if (!$tokenService->checkToken($csrfToken)) {
            $this->logCsrfFailure('Invalid token received', $csrfToken);
            return false;
        }

        $tokenService->revokeToken($csrfToken);

        try {
            $tokenService->addFormToken();
        } catch (\common_Exception $e) {
            return false;
        }

        return parent::validate();
    }

    /**
     * Log a failed CSRF validation attempt
     *
     * @param string $exceptionMessage
     * @param string|null $csrfToken
     * @throws \common_exception_Error
     */
    private function logCsrfFailure($exceptionMessage, $csrfToken = null)
    {
        $userIdentifier = common_session_SessionManager::getSession()->getUser()->getIdentifier();

        $this->logWarning(
            '[CSRF] - Failed to validate CSRF token. The following exception occurred: ' . $exceptionMessage
        );
        $this->logWarning(
            "[CSRF] \n" .
            "CSRF validation information: \n" .
            'Provided token: ' . ($csrfToken ?: 'none') . " \n" .
            'User identifier: ' . $userIdentifier . " \n" .
            'Form: ' . $this->name
        );
    }
}
