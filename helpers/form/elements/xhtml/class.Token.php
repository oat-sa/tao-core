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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

use oat\tao\model\security\xsrf\TokenService;
use oat\oatbox\service\ServiceManager;

/**
 * Special form element to handle tokens.
 * The posted value isn't kept, but a new token is generated.
 *
 * @author Bertrand Chevrier, <bertrand@taotesting.com>
 */
class tao_helpers_form_elements_xhtml_Token extends tao_helpers_form_elements_xhtml_Hidden
{
    public function render()
    {
        $tokenService = $this->getServiceManager()->get(TokenService::SERVICE_ID);

        //always add a new token
        $newToken = $tokenService->createToken();
        $this->setValue($newToken);
        Context::getInstance()->getResponse()->setCookie($tokenService->getTokenName(), $newToken, null, '/');

       return parent::render();
    }

    protected function getServiceManager()
    {
        return ServiceManager::getServiceManager();
    }
}
