<?php

error_reporting(E_ALL);

/**
 * Oauth Services based on the TAO DataStore implementation
 *
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes_oauth
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Service is the base class of all services, and implements the singleton
 * for derived services
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
require_once('tao/models/classes/class.Service.php');

/* user defined includes */
// section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003C9F-includes begin
require_once dirname(__FILE__).'/../../../lib/oauth/OAuth.php';
// section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003C9F-includes end

/* user defined constants */
// section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003C9F-constants begin
// section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003C9F-constants end

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
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

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

        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CBD begin
		$request = OAuthRequest::from_request();
		try {
			$this->validateOAuthRequest($request);
			$returnValue = true;
		} catch (OAuthException $e) {
			common_Logger::w($e->getMessage());
		}
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CBD end

        return (bool) $returnValue;
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
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CBB begin
        $server = new OAuthServer(new tao_models_classes_oauth_DataStore());
		$method = new OAuthSignatureMethod_HMAC_SHA1();
        $server->add_signature_method($method);
        
		$server->verify_request($request);
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CBB end
    }

} /* end of class tao_models_classes_oauth_Service */

?>