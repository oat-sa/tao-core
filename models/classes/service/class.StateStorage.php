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
    
    private $persistence;
    
	protected function __construct() {
		parent::__construct();
		$this->persistence = common_persistence_KeyValuePersistence::getPersistence(self::PERSISTENCE_ID);
	}
	
  	public function set($userId, $serial, $data) {
  	    $redisSerial = $this->getSerial($userId, $serial);
  	    $dataString = json_encode($data, true);
  	    return $this->persistence->set($redisSerial, $dataString);
  	}
  	
  	public function get($userId, $serial) {
  	    $redisSerial = $this->getSerial($userId, $serial);
  	    $returnValue = $this->persistence->get($redisSerial);
  	    if ($returnValue === false && !$this->has($userId, $serial)) {
  	        $returnValue = null;
  	    } else {
  	        $returnValue = json_decode($returnValue, true);
  	    }
  	    return $returnValue;
  	}
  	
  	public function has($userId, $serial) {
  	    $redisSerial = $this->getSerial($userId, $serial);
  	    return $this->persistence->exists($redisSerial);
  	}
  	
  	public function del($userId, $serial) {
  	    $redisSerial = $this->getSerial($userId, $serial);
  	    return $this->persistence->del($redisSerial);
  	}
  	
  	private function getSerial($userId, $serial) {
  	  		return $userId.'_'.$serial;;
  	}
  	 
}