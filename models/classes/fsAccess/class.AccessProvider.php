<?php
/**
 * 
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
 * Copyright (c) 2013 (original work) Open Assessment Techonologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */

/**
 * This is the base class of the Access Providers
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoItemRunner
 * @subpackage models_classes_itemAccess
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
abstract class tao_models_classes_fsAccess_AccessProvider
{
	/**
	 * @var core_kernel_fileSystem_FileSystem
	 */
	private $fileSystem = null;
	
	private $id;
	
	protected static function spawn(core_kernel_fileSystem_FileSystem $fileSystem, $customConfig = array()) {
	    $id = tao_models_classes_fsAccess_Manager::singleton()->reserveId();
	    $provider = new static($id, $fileSystem);
	    $provider->restoreConfig($customConfig);
	    tao_models_classes_fsAccess_Manager::singleton()->addProvider($provider);
	    return $provider;
	}
	
	private function __construct($id, core_kernel_fileSystem_FileSystem $fileSystem) {
	    $this->id = $id;
	    $this->fileSystem = $fileSystem;
	}

	public function getFileSystem() {
	    return $this->fileSystem;
	}

	public function getId() {
	    return $this->id;
	}
	
	protected function getBasePath() {
	    return $this->getFileSystem()->getPath();
	}
	
	public function serializeToString() {
	    return get_class($this).' '.$this->getId().' '.$this->getFileSystem()->getUri().' '.json_encode($this->getConfig());
	}
	
	public static function restoreFromString($string) {
	     list($class, $id, $fsUri, $config) = explode(' ', $string, 4);
	     $provider = new $class($id, new core_kernel_fileSystem_FileSystem($fsUri));
	     $provider->restoreConfig(json_decode($config, true));
	     return $provider;
	}
	
	protected abstract function getConfig();
	
	protected abstract function restoreConfig($config);
	
	public abstract function getAccessUrl($relativePath);
	
}