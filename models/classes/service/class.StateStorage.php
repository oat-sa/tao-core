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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */

/**
 * Persistence for the item delivery service
 *
 * @access public
 * @author @author Joel Bout, <joel@taotesting.com>
 * @package taoItemRunner
 * @subpackage models_classes_runner
 */
class tao_models_classes_service_StateStorage
    extends tao_models_classes_Service
{
    const PERSISTENCE_ID = 'serviceState';
    
    /**
     * Persistence to store service states to
     * 
     * @var common_persistence_KeyValuePersistence
     */
    private $persistence;
    
    /**
     * protected constructor to ensure singleton pattern
     */
	protected function __construct() {
		parent::__construct();
		$this->persistence = common_persistence_KeyValuePersistence::getPersistence(self::PERSISTENCE_ID);
	}
	
	/**
	 * Store the state of the service call
	 * 
	 * @param string $userId
	 * @param string $callId
	 * @param mixed $data
	 * @return boolean
	 */
  	public function set($userId, $callId, $data) {
  	    $key = $this->getSerial($userId, $callId);
  	    $dataString = json_encode($data, true);
  	    return $this->persistence->set($key, $dataString);
  	}
  	
  	/**
  	 * Retore the state of the service call
  	 * Returns null if no state is found
  	 * 
  	 * @param string $userId
  	 * @param string $callId
  	 * @return mixed
  	 */
  	public function get($userId, $callId) {
  	    $key = $this->getSerial($userId, $callId);
  	    $returnValue = $this->persistence->get($key);
  	    if ($returnValue === false && !$this->has($userId, $callId)) {
  	        $returnValue = null;
  	    } else {
  	        $returnValue = json_decode($returnValue, true);
  	    }
  	    return $returnValue;
  	}
  	
  	/**
  	 * Whenever or not a state for this service call exists
  	 * 
  	 * @param string $userId
  	 * @param string $callId
  	 * @return boolean
  	 */
  	public function has($userId, $callId) {
  	    $key = $this->getSerial($userId, $callId);
  	    return $this->persistence->exists($key);
  	}
  	
  	/**
  	 * Remove the state for this service call
  	 * 
  	 * @param string $userId
  	 * @param string $callId
  	 * @return boolean
  	 */
  	public function del($userId, $callId) {
  	    $key = $this->getSerial($userId, $callId);
  	    return $this->persistence->del($key);
  	}
  	
  	/**
  	 * Generate a storage key using the provide user and serial
  	 * 
  	 * @param string $userId
  	 * @param string $callId
  	 * @return string
  	 */
  	private function getSerial($userId, $callId) {
  	    if (is_object($userId)) {
  	        common_Logger::w('Object as userid: '.get_class($userId));
  	        if ($userId instanceof core_kernel_classes_Resource) {
  	            $userId = $userId->getUri();
  	        }
  	    }
  		return $userId.'_'.$callId;;
  	}
  	 
}