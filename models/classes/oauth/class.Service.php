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
 * Copyright (c) 2013 (original work) (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
use oat\oatbox\service\ServiceManager;
use oat\tao\model\oauth\OauthService;

/**
 * Oauth Services based on the TAO DataStore implementation
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @deprecated
 */
class tao_models_classes_oauth_Service
    implements common_http_SignatureService
{
    /**
     * Adds a signature to the request
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @param $authorizationHeader Move the signature parameters into the Authorization header of the request
     */
    public function sign(common_http_Request $request, common_http_Credentials $credentials, $authorizationHeader = false) {
        return $this->getService()->sign($request, $credentials, $authorizationHeader);
    }

    /**
     * Validates the signature of the current request
     *
     * @access protected
     * @author Joel Bout, <joel@taotesting.com>
     * @param  common_http_Request request
     * @throws common_Exception exception thrown if validation fails
    */
    public function validate(common_http_Request $request, common_http_Credentials $credentials = null) {
        return $this->getService()->validate($request, $credentials);
    }
    
    /**
     * @return OauthService
     */
    private function getService() {
        return ServiceManager::getServiceManager()->get(OauthService::SERVICE_ID);
    }
}