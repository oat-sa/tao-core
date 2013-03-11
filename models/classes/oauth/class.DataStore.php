<?php

error_reporting(E_ALL);

/**
 * Tao Implementation of an OAuthDatastore
 * Does not yet implement the nonce and request/access token
 *
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes_oauth
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include OAuthDataStore
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
//require_once('class.OAuthDataStore.php');

/* user defined includes */
// section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CA0-includes begin
require_once dirname(__FILE__).'/../../../includes/oauth/OAuth.php';
// section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CA0-includes end

/* user defined constants */
// section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CA0-constants begin
// section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CA0-constants end

/**
 * Tao Implementation of an OAuthDatastore
 * Does not yet implement the nonce and request/access token
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes_oauth
 */
class tao_models_classes_oauth_DataStore
    extends OAuthDataStore
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method findOauthConsumerResource
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @param  string consumer_key
     * @return core_kernel_classes_Resource
     */
    public function findOauthConsumerResource($consumer_key)
    {
        $returnValue = null;

        // section 10-30-1--78--71850a20:13d58c9a548:-8000:0000000000003CC4 begin
		$class = new core_kernel_classes_Class(CLASS_OAUTH_CONSUMER);
		$instances = $class->searchInstances(array(PROPERTY_OAUTH_KEY => $consumer_key), array('like' => false, 'recursive' => true));
		if (count($instances) == 0) {
			throw new tao_models_classes_oauth_Excpetion('No Credentials for consumer key '.$consumer_key);
		}
		if (count($instances) > 1) {
			throw new tao_models_classes_oauth_Excpetion('Multiple Credentials for consumer key '.$consumer_key);
		}
		$returnValue	= current($instances);
        // section 10-30-1--78--71850a20:13d58c9a548:-8000:0000000000003CC4 end

        return $returnValue;
    }

    /**
     * returns the OauthConsumer for the specified key
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @param  consumer_key
     * @return OAuthConsumer
     */
    public function lookup_consumer($consumer_key)
    {
        $returnValue = null;

        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CA5 begin
		$consumer = $this->findOauthConsumerResource($consumer_key);
		$secret			= (string)$consumer->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_OAUTH_SECRET));
		$callbackUrl	= null;
		
		$returnValue = new OAuthConsumer($consumer_key, $secret, $callbackUrl);
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CA5 end

        return $returnValue;
    }

    /**
     * Short description of method lookup_token
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @param  consumer
     * @param  token_type
     * @param  token
     * @return mixed
     */
    public function lookup_token($consumer, $token_type, $token)
    {
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CA7 begin
		common_Logger::d(__CLASS__.'::'.__FUNCTION__.' called');
		return new OAuthToken($consumer, "");
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CA7 end
    }

    /**
     * Short description of method lookup_nonce
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @param  consumer
     * @param  token
     * @param  nonce
     * @param  timestamp
     * @return mixed
     */
    public function lookup_nonce($consumer, $token, $nonce, $timestamp)
    {
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CA9 begin
        common_Logger::d(__CLASS__.'::'.__FUNCTION__.' called');
		return NULL;
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CA9 end
    }

    /**
     * Short description of method new_request_token
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @param  consumer
     * @return mixed
     */
    public function new_request_token($consumer)
    {
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CAB begin
        common_Logger::d(__CLASS__.'::'.__FUNCTION__.' called');
		return NULL;
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CAB end
    }

    /**
     * Short description of method new_access_token
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @param  token
     * @param  consumer
     * @return mixed
     */
    public function new_access_token($token, $consumer)
    {
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CAD begin
        common_Logger::d(__CLASS__.'::'.__FUNCTION__.' called');
		return NULL;
        // section 10-30-1--78-7fe2a05b:13d4a3616e9:-8000:0000000000003CAD end
    }

} /* end of class tao_models_classes_oauth_DataStore */

?>