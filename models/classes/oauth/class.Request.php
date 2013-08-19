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
 * Copyright (c) 20013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */

/**
 * Includes the Oauth library 
 */
require_once dirname(__FILE__).'/../../../lib/oauth/OAuth.php';

/**
 * Thin wrapper for the Oauth library
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes_oauth
 */
class tao_models_classes_oauth_Request
{

    /**
     * Creates an oauth request from the current parameters
     * 
     * @return tao_models_classes_oauth_Request
     */
    public static function fromRequest() {
        return new self(OAuthRequest::from_request());
    }
    
    /**
     * Create a new signed OAuth Request
     * 
     * @param core_kernel_classes_Resource $consumerResource
     * @param unknown $http_url
     * @param string $http_method
     * @param unknown $params
     * @return tao_models_classes_oauth_Request
     */
    public static function createSigned(core_kernel_classes_Resource $consumerResource, $http_url, $http_method = 'POST', $params = array())
    {
        $dataStore = new tao_models_classes_oauth_DataStore();
    
        $request = new OAuthRequest($http_method, $http_url);
        $consumer = $dataStore->getOauthConsumer($consumerResource);
        $token = $dataStore->new_request_token($consumer);
    
        $request = OAuthRequest::from_consumer_and_token($consumer, $token, 'POST', $http_url, $params);
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
        $request->sign_request($signature_method, $consumer, $token);
        
        return new self($request);
    }    
    
    private $oauthRequest;
    
    protected function __construct(OAuthRequest $request) {
        $this->oauthRequest = $request;
    }
    
    public function isValid() {
        $returnValue = false;
        try {
            $server = new OAuthServer(new tao_models_classes_oauth_DataStore());
    		$method = new OAuthSignatureMethod_HMAC_SHA1();
            $server->add_signature_method($method);
            
    		$server->verify_request($this->oauthRequest);
            $returnValue = true;
        } catch (OAuthException $e) {
            common_Logger::w($e->getMessage());
        } catch (tao_models_classes_oauth_Exception $e) {
            // no action nescessary, logged in exception
        }
        return (bool) $returnValue;
    }

    public function getParamters() {
        return $this->oauthRequest->get_parameters();
    }
}