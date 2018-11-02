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
namespace oat\tao\model\oauth;

use IMSGlobal\LTI\OAuth\OAuthConsumer;
use IMSGlobal\LTI\OAuth\OAuthSignatureMethod_HMAC_SHA1;
use IMSGlobal\LTI\OAuth\OAuthRequest;
use IMSGlobal\LTI\OAuth\OAuthServer;
use oat\oatbox\service\ConfigurableService;
use common_http_Request;
use IMSGlobal\LTI\OAuth\OAuthException;
/**
 * Oauth Services based on the TAO DataStore implementation
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class OauthService extends ConfigurableService implements \common_http_SignatureService
{
    const SERVICE_ID = 'tao/OauthService';
    
    const OPTION_DATASTORE = 'store';
    
    /**
     * Adds a signature to the request
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     *
     *
     * @param common_http_Request $request
     * @param \common_http_Credentials $credentials
     * @param bool $authorizationHeader Move the signature parameters into the Authorization header of the request
     *
     * @return common_http_Request
     *
     * @throws \tao_models_classes_oauth_Exception
     */
    public function sign(common_http_Request $request, \common_http_Credentials $credentials, $authorizationHeader = false) {
        
        if (!$credentials instanceof \tao_models_classes_oauth_Credentials) {
            throw new \tao_models_classes_oauth_Exception('Invalid credentals: '.gettype($credentials));
        }
        
        $dataStore = $this->getDataStore();
        $consumer = $dataStore->getOauthConsumer($credentials);
        $token = $dataStore->new_request_token($consumer);

        return $this->signFromConsumerAndToken($request, $consumer, $token, $authorizationHeader);
    }

    /**
     * @param common_http_Request $request
     * @param OAuthConsumer $consumer
     * @param $token
     * @param bool $authorizationHeader
     *
     * @return common_http_Request
     */
    public function signFromConsumerAndToken(common_http_Request $request, OAuthConsumer $consumer, $token = null, $authorizationHeader = false)
    {
        $oauthRequest = $this->getOauthRequest($request);

        $allInitialParameters = $this->getAllInitialParameters($request, $authorizationHeader);
        $signedRequest = $this->signRequest($consumer, $token, $oauthRequest, $allInitialParameters);

        return $this->finalizeSignedRequest($signedRequest, $request, $allInitialParameters, $authorizationHeader);
    }

    /**
     * @param common_http_Request $originalRequest
     * @param bool $authorizationHeader
     *
     * @return array
     */
    protected function getAllInitialParameters(common_http_Request $originalRequest, $authorizationHeader)
    {
        $allInitialParameters = array();
        $allInitialParameters = array_merge($allInitialParameters, $originalRequest->getParams());
        $allInitialParameters = array_merge($allInitialParameters, $originalRequest->getHeaders());

        //oauth_body_hash is used for the signing computation
        if ($authorizationHeader) {
            $oauth_body_hash = base64_encode(sha1($originalRequest->getBody(), true));//the signature should be ciomputed from encoded versions
            $allInitialParameters = array_merge($allInitialParameters, array("oauth_body_hash" =>$oauth_body_hash));
        }

        return $allInitialParameters;
    }

    /**
     * @param OAuthConsumer $consumer
     * @param $token
     * @param OAuthRequest $oauthRequest
     * @param array $parameters
     *
     * @return OAuthRequest
     */
    protected function signRequest(OAuthConsumer $consumer, $token, OAuthRequest $oauthRequest, array $parameters)
    {
        $signedRequest = OAuthRequest::from_consumer_and_token(
            $consumer,
            $token,
            $oauthRequest->get_normalized_http_method(),
            $oauthRequest->to_url(),
            $parameters
        );

        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
        $signedRequest->sign_request($signature_method, $consumer, $token);

        return $signedRequest;
    }

    /**
     * @param OAuthRequest $signedRequest
     * @param common_http_Request $originalRequest
     * @param array $parameters
     * @param bool $authorizationHeader
     *
     * @return common_http_Request
     */
    protected function finalizeSignedRequest(OAuthRequest $signedRequest, common_http_Request $originalRequest, array $parameters, $authorizationHeader)
    {
        if ($authorizationHeader) {
            $combinedParameters = $signedRequest->get_parameters();
            $signatureParameters = array_diff_assoc($combinedParameters, $parameters);

            $signatureParameters["oauth_body_hash"] = base64_encode(sha1($originalRequest->getBody(), true));
            $signatureHeaders = array("Authorization" => $this->buildAuthorizationHeader($signatureParameters));
            $finalizedSignedRequest = new common_http_Request(
                $signedRequest->to_url(),
                $signedRequest->get_normalized_http_method(),
                $originalRequest->getParams(),
                array_merge($signatureHeaders, $originalRequest->getHeaders()),
                $originalRequest->getBody()
            );
        } else {
            $finalizedSignedRequest =  new common_http_Request(
                $signedRequest->to_url(),
                $signedRequest->get_normalized_http_method(),
                $signedRequest->get_parameters(),
                $originalRequest->getHeaders(),
                $originalRequest->getBody()
            );
        }

        return $finalizedSignedRequest;
    }

    /**
     * Validates the signature of the current request
     *
     * @access protected
     * @author Joel Bout, <joel@taotesting.com>
     * @param  common_http_Request request
     * @throws common_Exception exception thrown if validation fails
    */
    public function validate(common_http_Request $request, \common_http_Credentials $credentials = null) {
        $server = new OAuthServer($this->getDataStore());
		$method = new OAuthSignatureMethod_HMAC_SHA1();
        $server->add_signature_method($method);
        
        try {
            $oauthRequest = $this->getOauthRequest($request);
            $server->verify_request($oauthRequest);
        } catch (OAuthException $e) {
            throw new \common_http_InvalidSignatureException('Validation failed: '.$e->getMessage());
        }
    }

    /**
     * @return DataStore
     */
    public function getDataStore() {
        return $this->getSubService(self::OPTION_DATASTORE);
    }

    /**
     * As per the OAuth body hashing specification, all of the OAuth parameters must be sent as part of the Authorization header.
     *  In particular, OAuth parameters from the request URL and POST body will be ignored.
     * Return the Authorization header
     */
    private function buildAuthorizationHeader($signatureParameters) {
        $authorizationHeader = 'OAuth realm=""';
        
        foreach ($signatureParameters as $key=>$value) {
            $authorizationHeader.=','.$key."=".'"'.urlencode($value).'"';
        }
        return $authorizationHeader;
    }

    /**
     * Transform common_http_Request into an OAuth request
     * @param common_http_Request $request
     * @return \IMSGlobal\LTI\OAuth\OAuthRequest
     */
    private function getOauthRequest(common_http_Request $request) {
        $params = array();
        
        $params = array_merge($params, $request->getParams());
        //$params = array_merge($params, $request->getHeaders());
        \common_Logger::d("OAuth Request created:".$request->getUrl()." using ".$request->getMethod());
        $oauthRequest = new OAuthRequest($request->getMethod(), $request->getUrl(), $params);
        return $oauthRequest;
    }
}