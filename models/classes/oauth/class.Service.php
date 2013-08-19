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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
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
    extends tao_models_classes_Service
{

    /**
     * returns whenever or not the current request is a valid Oauth request
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @return boolean
     */
    public function isCurrentRequestValid()
    {
        $returnValue = (bool) false;

		$request = OAuthRequest::from_request();
		try {
			$this->validateOAuthRequest($request);
			$returnValue = true;
		} catch (OAuthException $e) {
			common_Logger::w($e->getMessage());
		} catch (tao_models_classes_oauth_Exception $e) {
			// no action nescessary, logged in exception
		}

        return (bool) $returnValue;
    }

    public function isCurrentRequestValid()
    {
    }
    /**
     * validates an OAuthRequest
     *
     * @access protected
     * @author Joel Bout, <joel@taotesting.com>
     * @param  OAuthRequest request
     * @return mixed
     */
    protected function validateOAuthRequest( OAuthRequest $request)
    {
        $server = new OAuthServer(new tao_models_classes_oauth_DataStore());
		$method = new OAuthSignatureMethod_HMAC_SHA1();
        $server->add_signature_method($method);
        
		$server->verify_request($request);
    }

    /**
     * Takes request, including parameters, signs it
     * and returns the parameters including the signature
     * 
     * @param core_kernel_classes_Resource $consumerResource
     * @param string $http_url
     * @param string $http_method
     * @param array $params
     */
    public function getSignedRequestParameters(core_kernel_classes_Resource $consumerResource, $http_url, $http_method = 'POST', $params = array())
    {
        $dataStore = new tao_models_classes_oauth_DataStore();
        
        $request = new OAuthRequest($http_method, $http_url);
        $consumer = $dataStore->getOauthConsumer($consumerResource);
        $token = $dataStore->new_request_token($consumer);
        
        $request = OAuthRequest::from_consumer_and_token($consumer, $token, 'POST', $http_url, $params);
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
        common_logger::d('Base string: '.$request->get_signature_base_string());
        $request->sign_request($signature_method, $consumer, $token);
        
        return $request->get_parameters();
    }
}