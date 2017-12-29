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
 *			   2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);\n *			   2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *             2013 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
namespace oat\tao\model\oauth;

use oat\tao\model\TaoOntology;
use IMSGlobal\LTI\OAuth\OAuthDataStore;
use IMSGlobal\LTI\OAuth\OAuthConsumer;
use IMSGlobal\LTI\OAuth\OAuthToken;
use oat\oatbox\service\ConfigurableService;
use oat\generis\model\OntologyAwareTrait;

/**
 * Tao Implementation of an OAuthDatastore
 * Does not yet implement the nonce and request/access token
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class DataStore extends ConfigurableService
{
    use OntologyAwareTrait;
    
    const OPTION_NONCE_STORE = 'nonce';

    const CLASS_URI_OAUTH_CONSUMER = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthConsumer';
    const PROPERTY_OAUTH_KEY = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthKey';
    const PROPERTY_OAUTH_SECRET = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthSecret';
    const PROPERTY_OAUTH_CALLBACK = 'http://www.tao.lu/Ontologies/TAO.rdf#OauthCallbackUrl';

	/**
	 * Helper function to find the OauthConsumer RDF Resource
	 *
	 * @access public
	 * @author Joel Bout, <joel@taotesting.com>
	 * @param  string consumer_key
	 * @return \core_kernel_classes_Resource
	 */
	public function findOauthConsumerResource($consumer_key)
	{
		$returnValue = null;

		$class = $this->getClass(self::CLASS_URI_OAUTH_CONSUMER);
		$instances = $class->searchInstances(array(self::PROPERTY_OAUTH_KEY => $consumer_key), array('like' => false, 'recursive' => true));
		if (count($instances) == 0) {
			throw new \tao_models_classes_oauth_Exception('No Credentials for consumer key '.$consumer_key);
		}
		if (count($instances) > 1) {
			throw new \tao_models_classes_oauth_Exception('Multiple Credentials for consumer key '.$consumer_key);
		}
		$returnValue	= current($instances);

		return $returnValue;
	}
	
	/**
	 * Returns the OAuthConsumer for the provided credentials
	 *
	 * @param \common_http_Credentials $consumer
	 * @throws \tao_models_classes_oauth_Exception
	 * @return \IMSGlobal\LTI\OAuth\OAuthConsumer
	 */
	public function getOauthConsumer(\common_http_Credentials $credentials)
	{
        if (!$credentials instanceof \core_kernel_classes_Resource) {
            throw new \tao_models_classes_oauth_Exception('Unsupported credential type '.get_class($credentials));
        }
	    $values = $credentials->getPropertiesValues(array(
			self::PROPERTY_OAUTH_KEY,
			self::PROPERTY_OAUTH_SECRET,
			self::PROPERTY_OAUTH_CALLBACK
	    ));
	    if (empty($values[self::PROPERTY_OAUTH_KEY]) || empty($values[self::PROPERTY_OAUTH_SECRET])) {
	        throw new \tao_models_classes_oauth_Exception('Incomplete oauth consumer definition for '.$credentials->getUri());
	    }
	    $consumer_key = (string)current($values[self::PROPERTY_OAUTH_KEY]);
	    $secret = (string)current($values[self::PROPERTY_OAUTH_SECRET]);
	    if (!empty($values[self::PROPERTY_OAUTH_CALLBACK])) {
	        $callbackUrl = (string)current($values[self::PROPERTY_OAUTH_CALLBACK]);
	        if (empty($callbackUrl)) {
	            $callbackUrl = null;
	        }
	    } else {
	        $callbackUrl = null;
	    }
        return new OAuthConsumer($consumer_key, $secret, $callbackUrl);
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

		$consumer = $this->findOauthConsumerResource($consumer_key);
		$secret			= (string)$consumer->getUniquePropertyValue($this->getProperty(self::PROPERTY_OAUTH_SECRET));
		$callbackUrl	= null;
		
		$returnValue = new OAuthConsumer($consumer_key, $secret, $callbackUrl);

		return $returnValue;
	}

	/**
	 * Should verify if the token exists and return it
	 * Always returns an token with an empty secret for now
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
  		\common_Logger::d(__CLASS__.'::'.__FUNCTION__.' called for token '.$token.' of type '.$token_type);
		return new OAuthToken($consumer, "");
	}

	/**
	 * Should verify if a nonce has already been used
	 * always return NULL, meaning that nonces can be reused
	 *
	 * @access public
	 * @author Joel Bout, <joel@taotesting.com>
	 * @param OAuthConsumer $consumer
	 * @param OAuthToken $token
	 * @param string $nonce
	 * @param string $timestamp
	 * @return mixed
	 */
	public function lookup_nonce($consumer, $token, $nonce, $timestamp)
	{
        $store = $this->getSubService(self::OPTION_NONCE_STORE);
        return $store->isValid($timestamp.'_'.$consumer->key.'_'.$nonce) ? null : true;
	}

	/**
	 * Should create a new request token
	 * not implemented
	 *
	 * @access public
	 * @author Joel Bout, <joel@taotesting.com>
	 * @param  consumer
	 * @param  callback
	 * @return mixed
	 */
	function new_request_token($consumer, $callback = null)
	{
		\common_Logger::d(__CLASS__.'::'.__FUNCTION__.' called');
		return null;
	}

	/**
	 * Should create a new access token
	 * not implemented
	 * 
	 * @access public
	 * @author Joel Bout, <joel@taotesting.com>
	 * @param  token
	 * @param  consumer
	 * @return mixed
	 */
	public function new_access_token($token, $consumer, $verifier = null)
	{
		\common_Logger::d(__CLASS__.'::'.__FUNCTION__.' called');
		return null;
	}

}