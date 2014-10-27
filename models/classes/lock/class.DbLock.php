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
 * Copyright (c) 2013 Open Assessment Technologies S.A.
 * 
 */


/**
 * Implements Lock using a basic property in the ontology storing the lock data
 *
 * @note It would be preferably static but we may want to have the polymorphism on lock but it would be prevented by explicit class method static calls.
 * Also if you nevertheless call it statically you may want to avoid the late static binding for the getLockProperty
 */
class tao_models_classes_lock_DbLock
    implements tao_models_classes_lock_Lock
{
    private $isEnabled = false ;
    
    private static $instance;
    /**
     * 
     * @return core_kernel_classes_Property
     */
    private function getLockProperty(){
        return new core_kernel_classes_Property(PROPERTY_LOCK);
    }
    /**
     * 
     * @return tao_models_classes_lock_DbLock
     */
    public static function singleton() {
        $returnValue = null;
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        $returnValue = self::$instance;
        return $returnValue;
    }
    /**
     * 
     * @author Lionel Lecaque <lionel@taotesting.com>
     */
    private function __construct()
    {
        //read status from config
        $this->restoreEnabled();
    }
    /**
     * 
     * @author Lionel Lecaque <lionel@taotesting.com>
     * @return boolean
     */
    private function isEnabled() {
        return $this->isEnabled;
    }
    /**
     * 
     * @author Lionel Lecaque <lionel@taotesting.com>
     * @param boolean $isEnabled
     */
    public function setEnabled($isEnabled){
        $this->isEnabled = $isEnabled;
    }
    
    /**
     * 
     * @author Lionel Lecaque <lionel@taotesting.com>
     */
    public function restoreEnabled(){
        if ((!defined('ENABLE_LOCK')) || (!(ENABLE_LOCK))) {
            $this->setEnabled(false);
        } else {
            $this->setEnabled(true);
        }
    }
    /**
     * set a lock on @resource with owner @user, succeeds also if there is a lock already exists but with the same owner
     *
     * @throw common_Exception if the resource already has a lock with a different owner
     * @param core_kernel_classes_Resource $resource
     * @param core_kernel_classes_Resource $owner
     */
    public function setLock(core_kernel_classes_Resource $resource, core_kernel_classes_Resource $owner){
        if (!$this->isEnabled()) {
            return false;
        }
        if ($this->isLocked($resource) !== false) {
            throw new common_Exception($resource->getUri() . ' is already locked');
        }

        $lock = new tao_models_classes_lock_LockData($resource, $owner, microtime(true));

        $dbWrapper = core_kernel_classes_DbWrapper::singleton();
        $dbWrapper->insert('locks', [
            'resource_uri' => $resource->getUri(),
            'lock_data'    => $lock->toJson()
        ]);
    }

    /**
	 * Check if the resource is been locked
	 *
	 * @param core_kernel_classes_Resource $resource        	
     * @return boolean TRUE if locked, FALSE otherwise
     */
    public function isLocked(core_kernel_classes_Resource $resource) {
        if (!$this->isEnabled()) {
            common_Logger::d('Lock is disable : return false');
            return false;
        }

        $lock = $this->getLockData($resource);

        return $lock !== false;
    }

	/**
	 * release the lock if owned by @user
	 *
	 * @param core_kernel_classes_Resource $resource        	
	 * @param core_kernel_classes_Resource $user
	 * @throws common_exception_Unauthorized
     * @return TRUE on success FALSE on failure
	 */
	public function releaseLock(core_kernel_classes_Resource $resource, core_kernel_classes_Resource $user) {
		$lockData = $this->getLockData($resource);
		if ($lockData === false) {
			return false;
		}

        $ownerUri = $lockData->getOwner()->getUri();

        if ($ownerUri != $user->getUri()) {

            // user is not the owner, check if admin

            $role = new core_kernel_classes_Resource(INSTANCE_ROLE_SYSADMIN);
            if (!tao_models_classes_UserService::singleton()->userHasRoles($user, $role)) {
                // user is not an admin either
				throw new common_exception_Unauthorized ( "The resource is owned by " . $lockData->getOwner () );
            }

        }

        return $this->forceReleaseLock($resource);
	}

   /**
    *  release the lock
    * @param core_kernel_classes_Resource $resource
    */
    public function forceReleaseLock(core_kernel_classes_Resource $resource){
        $dbWrapper = core_kernel_classes_DbWrapper::singleton();
        $dbWrapper->query(
            'DELETE FROM locks WHERE resource_uri = ?', [$resource->getUri()]
        );
        return true;
    }

    /**
     * Return lock details
     * @param core_kernel_classes_Resource $resource
     * @throws common_Exception
     * @return tao_helpers_lock_LockData on success or FALSE on failure
     */
    public function getLockData(core_kernel_classes_Resource $resource) {
        $dbWrapper = core_kernel_classes_DbWrapper::singleton();
        $statement = $dbWrapper->getPlatForm()->limitStatement(
            'SELECT lock_data FROM locks WHERE resource_uri = ?', 1
        );
        $result = $dbWrapper->query($statement, [$resource->getUri()]);
        if (!$result->rowCount()) {
            return false;
        }
        $lock = $result->fetchColumn();
        $result->closeCursor();

        return tao_models_classes_lock_LockData::getLockData($lock);

    }
}

?>