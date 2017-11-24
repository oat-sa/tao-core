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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\helpers\form\validators;

use oat\tao\model\security\xsrf\TokenService;
use oat\oatbox\service\ServiceManager;

/**
 * Validate a token
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class XsrfTokenValidator extends \tao_helpers_form_Validator
{

    /**
     * Validate an active XSRF token.
     *
     * @param string $values should be the token
     * @return boolean true only if valid
     * @throws \common_exception_Unauthorized if the token is not valid
     */
    public function evaluate($values)
    {
        $tokenService = $this->getServiceManager()->get(TokenService::SERVICE_ID);

        if($tokenService->checkToken($values)){
            $tokenService->revokeToken($values);
            return true;
        }

        \common_Logger::e('Attempt to post a form with the incorrect token');
        throw new \common_exception_Unauthorized('Invalid token '. $values);
    }

    protected function getServiceManager()
    {
        return ServiceManager::getServiceManager();
    }
}
