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

use common_http_Credentials;
use common_http_InvalidSignatureException;
use IMSGlobal\LTI\OAuth\OAuthSignatureMethod_HMAC_SHA1;
use IMSGlobal\LTI\OAuth\OAuthRequest;
use IMSGlobal\LTI\OAuth\OAuthServer;
use IMSGlobal\LTI\OAuth\OAuthUtil;
use oat\oatbox\service\ConfigurableService;
use common_http_Request;
use IMSGlobal\LTI\OAuth\OAuthException;
use oat\oatbox\service\exception\InvalidService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\model\oauth\lockout\LockOutException;
use oat\tao\model\oauth\lockout\LockoutInterface;
use Psr\Http\Message\ServerRequestInterface;
use tao_models_classes_oauth_Exception;

/**
 * Oauth Services based on the TAO DataStore implementation
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class OauthService extends ConfigurableService implements \common_http_SignatureService
{
    public const SERVICE_ID = 'tao/OauthService';

    public const OPTION_LOCKOUT_SERVICE = 'lockout';
    public const OPTION_DATA_STORE = 'store';

    protected const OAUTH_BODY_HASH_PARAM = 'oauth_body_hash';
    protected const OAUTH_CONSUMER_KEY = 'oauth_consumer_key';

    //oauth_consumer_secret

    /**
     * Adds a signature to the request
     *
     * @access public
     *
     * @param common_http_Request     $request
     * @param common_http_Credentials $credentials
     * @param                         $authorizationHeader boolean Move the signature parameters into the Authorization header of the request
     *
     * @return common_http_Request
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     * @throws tao_models_classes_oauth_Exception
     * @author Joel Bout, <joel@taotesting.com>
     */
    public function sign(common_http_Request $request, common_http_Credentials $credentials, $authorizationHeader = false)
    {

        if (!$credentials instanceof \tao_models_classes_oauth_Credentials) {
            throw new tao_models_classes_oauth_Exception('Invalid credentals: ' . gettype($credentials));
        }

        $oauthRequest = $this->getOauthRequest($request);
        $dataStore = $this->getDataStore();
        $consumer = $dataStore->getOauthConsumer($credentials);
        $token = $dataStore->new_request_token($consumer);

        $allInitialParameters = array_merge($request->getParams(), $request->getHeaders());

        //oauth_body_hash is used for the signing computation
        if ($authorizationHeader) {
            // the signature should be computed from encoded versions
            $oauthBodyHash = $this->calculateOauthBodyHash($request->getBody());
            $allInitialParameters = array_merge(
                $allInitialParameters,
                [self::OAUTH_BODY_HASH_PARAM => $oauthBodyHash]
            );
        }

        $signedRequest = OAuthRequest::from_consumer_and_token(
            $consumer,
            $token,
            $oauthRequest->get_normalized_http_method(),
            $oauthRequest->to_url(),
            $allInitialParameters
        );
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
        $signedRequest->sign_request($signature_method, $consumer, $token);
        //common_logger::d('Base string from TAO/Joel: '.$signedRequest->get_signature_base_string());

        if ($authorizationHeader) {
            $combinedParameters = $signedRequest->get_parameters();
            $signatureParameters = array_diff_assoc($combinedParameters, $allInitialParameters);

            // if $authorizationHeader is true then $oauthBodyHash should be defined
            /** @noinspection PhpUndefinedVariableInspection */
            $signatureParameters[self::OAUTH_BODY_HASH_PARAM] = $oauthBodyHash;
            $signatureHeaders = ["Authorization" => $this->buildAuthorizationHeader($signatureParameters)];
            $signedRequest = new common_http_Request(
                $signedRequest->to_url(),
                $signedRequest->get_normalized_http_method(),
                $request->getParams(),
                array_merge($signatureHeaders, $request->getHeaders()),
                $request->getBody()
            );
        } else {
            $signedRequest =  new common_http_Request(
                $signedRequest->to_url(),
                $signedRequest->get_normalized_http_method(),
                $signedRequest->get_parameters(),
                $request->getHeaders(),
                $request->getBody()
            );
        }

        return $signedRequest;
    }

    /**
     * Validates the signature of the current request
     *
     * @param common_http_Request          $request
     * @param common_http_Credentials|null $credentials
     *
     * @return array [OAuthConsumer, token]
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     * @throws common_http_InvalidSignatureException
     * @throws LockOutException
     * @author Joel Bout, <joel@taotesting.com>
     */
    public function validate(common_http_Request $request, common_http_Credentials $credentials = null)
    {
        $server = new OAuthServer($this->getDataStore());
        $method = new OAuthSignatureMethod_HMAC_SHA1();
        $server->add_signature_method($method);

        $oauthRequest = $this->getOauthRequest($request);
        $oauthBodyHash = $oauthRequest->get_parameter(self::OAUTH_BODY_HASH_PARAM);
        if ($oauthBodyHash !== null && !$this->validateBodyHash($request->getBody(), $oauthBodyHash)) {
            throw new common_http_InvalidSignatureException('Validation failed: invalid body hash');
        }
        /** @var LockoutInterface $lockoutService */
        $lockoutService = $this->getSubService(self::OPTION_LOCKOUT_SERVICE);
        try {
            if(!$lockoutService->isAllowed()){
                throw new LockOutException('Blocked');
            }
            return $server->verify_request($oauthRequest);
        } catch (OAuthException $e) {

            /** @var LockoutInterface $lockoutService */
            $lockoutService = $this->getSubService(self::OPTION_LOCKOUT_SERVICE);
            $lockoutService->logFailedAttempt();

            throw new common_http_InvalidSignatureException('Validation failed: ' . $e->getMessage());
        }
    }

    /**
     * Wrapper over parent validate method to support PSR Request object
     *
     * @param ServerRequestInterface       $request
     * @param common_http_Credentials|null $credentials
     *
     * @return array [OAuthConsumer, token]
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     * @throws common_http_InvalidSignatureException
     */
    public function validatePsrRequest(ServerRequestInterface $request, common_http_Credentials $credentials = null)
    {
        $oldRequest = $this->buildCommonRequestFromPsr($request);
        return $this->validate($oldRequest, $credentials);
    }

    /**
     * @return ImsOauthDataStoreInterface
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function getDataStore()
    {
        return $this->getSubService(self::OPTION_DATA_STORE);
    }

    /**
     * @param ServerRequestInterface $request
     * @return common_http_Request
     */
    private function buildCommonRequestFromPsr(ServerRequestInterface $request)
    {
        $body = (string) $request->getBody();
        // https://tools.ietf.org/html/rfc5849#section-3.4.1.3.1
        $contentTypeHeaders = $request->getHeader('Content-Type');
        $params = reset($contentTypeHeaders) === 'application/x-www-form-urlencoded'
            ? OAuthUtil::parse_parameters($body)
            : [];

        return new common_http_Request(
            $request->getUri(),
            $request->getMethod(),
            $params,
            $request->getHeaders(),
            $body
        );
    }

    /**
     * Check if $bodyHash is valid hash for $body contents
     * @param string $body
     * @param string $bodyHash
     * @return bool
     */
    protected function validateBodyHash($body, $bodyHash)
    {
        // Check should be added here after ensuring it will not break existing LTI clients
        // This method was initially added to be overwritten in \oat\taoLti\models\classes\Lis\LisOauthService
        // where we need to perform real check
        return true;
    }

    /**
     * @param string $body
     * @return string
     */
    protected function calculateOauthBodyHash($body)
    {
        return base64_encode(sha1($body, true));
    }

    /**
     * As per the OAuth body hashing specification, all of the OAuth parameters must be sent as part of the Authorization header.
     *  In particular, OAuth parameters from the request URL and POST body will be ignored.
     * Return the Authorization header
     */
    private function buildAuthorizationHeader($signatureParameters)
    {
        $authorizationHeader = 'OAuth realm=""';

        foreach ($signatureParameters as $key => $value) {
            $authorizationHeader .= ',' . $key . '=' . '"' . urlencode($value) . '"';
        }
        return $authorizationHeader;
    }

    /**
     * Transform common_http_Request into an OAuth request
     * @param common_http_Request $request
     * @return OAuthRequest
     */
    private function getOauthRequest(common_http_Request $request)
    {
        $params = [];

        // In LTI launches oauth params are passed as POST params, but in LIS requests
        // they located in Authorization header. We try to extract them for further verification
        $authHeader = $request->getHeaderValue('Authorization');
        if (!empty($authHeader)) {
            $params = OAuthUtil::split_header($authHeader[0]);
        }

        $params = array_merge($params, $request->getParams());

        \common_Logger::d('OAuth Request created:' . $request->getUrl() . ' using ' . $request->getMethod());

        return new OAuthRequest($request->getMethod(), $request->getUrl(), $params);
    }
}
