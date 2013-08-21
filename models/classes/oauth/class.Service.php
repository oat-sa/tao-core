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

/**
 * Includes the Oauth library 
 */
require_once dirname(__FILE__).'/../../../lib/oauth/OAuth.php';

/**
 * Oauth Services based on the TAO DataStore implementation
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes_oauth
 */
class tao_models_classes_oauth_Service
    implements common_http_SignatureService
{
    /**
     * Adds a signature to the request
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     */
    public function sign(common_http_Request $request, common_http_Credentials $credentials) {
        
        if (!$credentials instanceof tao_models_classes_oauth_Credentials) {
            throw new tao_models_classes_oauth_Exception('Invalid credentals: '.gettype($credentials));
        }
        
        $oauthRequest = $this->getOauthRequest($request); 
        $dataStore = new tao_models_classes_oauth_DataStore();
        $consumer = $dataStore->getOauthConsumer($credentials);
        $token = $dataStore->new_request_token($consumer);

        $signedRequest = OAuthRequest::from_consumer_and_token(
            $consumer,
            $token,
            $oauthRequest->get_normalized_http_method(),
            $oauthRequest->get_normalized_http_url(),
            $oauthRequest->get_parameters()
        );
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
        common_logger::d('Base string: '.$signedRequest->get_signature_base_string());
        $signedRequest->sign_request($signature_method, $consumer, $token);
        
        return new common_http_Request(
            $signedRequest->get_normalized_http_url(),
            $signedRequest->get_normalized_http_method(),
            $signedRequest->get_parameters()
        );
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
        $server = new OAuthServer(new tao_models_classes_oauth_DataStore());
		$method = new OAuthSignatureMethod_HMAC_SHA1();
        $server->add_signature_method($method);
        
        try {
            $oauthRequest = $this->getOauthRequest($request);
            $server->verify_request($oauthRequest);
        } catch (OAuthException $e) {
            throw new common_http_InvalidSignatureException('Validation failed: '.$e->getMessage());
        }
    }
    
    private function getOauthRequest(common_http_Request $request) {
        $params = array();
        
        $params = array_merge($params, $request->getParams());
        //$params = array_merge($params, $request->getHeaders());
        
        $oauthRequest = new OAuthRequest($request->getMethod(), $request->getUrl(), $params);
        return $oauthRequest;
    }
}