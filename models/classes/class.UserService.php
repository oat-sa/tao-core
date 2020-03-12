<?php

/*
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
 *               2013-2014 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\user\LoginService;
use oat\tao\model\event\UserCreatedEvent;
use oat\tao\model\event\UserRemovedEvent;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\TaoRoles;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\event\UserUpdatedEvent;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\ClassServiceTrait;
use oat\tao\model\GenerisServiceTrait;

/**
 * This class provide service on user management
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 */
class tao_models_classes_UserService extends ConfigurableService implements core_kernel_users_UsersManagement
{
    use ClassServiceTrait;
    use GenerisServiceTrait {
        createInstance as protected traitCreateInstance;
    }


    const SERVICE_ID = 'tao/UserService';
    const OPTION_ALLOW_API = 'allow_api';
    /**
     * the core user service
     *
     * @access protected
     * @var core_kernel_users_Service
     */
    protected $generisUserService = null;

    /**
     * @deprecated
     */
    public static function singleton()
    {
        return ServiceManager::getServiceManager()->get(self::SERVICE_ID);
    }

    /**
     * constructor
     *
     * @param array $options
     * @return mixed
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function __construct($options = [])
    {
        parent::__construct($options);
        $this->generisUserService = core_kernel_users_Service::singleton();
    }

    /**
     * authenticate a user
     *
     * @access public
     * @param string login
     * @param string password
     * @return boolean
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @deprecated
     */
    public function loginUser($login, $password)
    {
        $returnValue = (bool)false;
        try {
            $returnValue = LoginService::login($login, $password);
        } catch (core_kernel_users_Exception $ue) {
            common_Logger::e("A fatal error occured at user login time: " . $ue->getMessage());
        }
        return (bool)$returnValue;
    }

    /**
     * retrieve the logged in user
     *
     * @access public
     * @return core_kernel_classes_Resource
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function getCurrentUser()
    {
        $returnValue = null;
        if (!common_session_SessionManager::isAnonymous()) {
            $userUri = \common_session_SessionManager::getSession()->getUser()->getIdentifier();
            if (!empty($userUri)) {
                $returnValue = new core_kernel_classes_Resource($userUri);
            } else {
                common_Logger::d('no userUri');
            }
        }

        return $returnValue;
    }

    /**
     * Check if the login is already used
     *
     * @access public
     * @param string login
     * @param
     * @return boolean
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function loginExists($login, core_kernel_classes_Class $class = null)
    {
        $returnValue = (bool)false;
        $returnValue = $this->generisUserService->loginExists($login, $class);
        return (bool)$returnValue;
    }

    /**
     * Check if the login is available (because it's unique)
     *
     * @access public
     * @param string login
     * @return boolean
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function loginAvailable($login)
    {
        $returnValue = (bool)false;
        if (!empty($login)) {
            $returnValue = !$this->loginExists($login);
        }

        return (bool)$returnValue;
    }

    /**
     * Get a user that has a given login.
     *
     * @access public
     * @param string login the user login is the unique identifier to retrieve him.
     * @param core_kernel_classes_Class A specific class to search the user.
     * @return core_kernel_classes_Resource
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function getOneUser($login, core_kernel_classes_Class $class = null)
    {
        $returnValue = null;
        if (empty($login)) {
            throw new common_exception_InvalidArgumentType('Missing login for ' . __FUNCTION__);
        }

        $class = (!empty($class)) ? $class : $this->getRootClass();

        $user = $this->generisUserService->getOneUser($login, $class);

        if (!empty($user)) {
            $userRolesProperty = new core_kernel_classes_Property(GenerisRdf::PROPERTY_USER_ROLES);
            $userRoles = $user->getPropertyValuesCollection($userRolesProperty);
            $allowedRoles = $this->getAllowedRoles();

            if ($this->generisUserService->userHasRoles($user, $allowedRoles)) {
                $returnValue = $user;
            } else {
                common_Logger::i('User found for login \'' . $login . '\' but does not have matchign roles');
            }
        } else {
            common_Logger::i('No user found for login \'' . $login . '\'');
        }

        return $returnValue;
    }

    /**
     * @param $userId
     * @return \oat\oatbox\user\User
     * @throws common_exception_Error
     */
    public function getUserById($userId)
    {
        if (is_string($userId)) {
            $userId = new \core_kernel_classes_Resource($userId);
        }

        if ($userId instanceof core_kernel_classes_Resource) {
            $userId = new core_kernel_users_GenerisUser($userId);
        }

        if (!($userId instanceof core_kernel_users_GenerisUser)) {
            \common_Logger::i('Unable to get user from ' . $userId);
            $userId = null;
        }

        return $userId;
    }

    /**
     * Remove a user
     *
     * @access public
     * @param core_kernel_classes_Resource $user
     * @return boolean
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function removeUser(core_kernel_classes_Resource $user)
    {
        $returnValue = (bool)false;
        if (!is_null($user)) {
            $this->checkCurrentUserAccess($this->getUserRoles($user));
            $returnValue = $this->generisUserService->removeUser($user);
            $this->getEventManager()->trigger(new UserRemovedEvent($user->getUri()));
        }

        return (bool)$returnValue;
    }

    /**
     * returns a list of all concrete roles(instances of GenerisRdf::CLASS_ROLE)
     * which are allowed to login
     *
     * @access public
     * @return array
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function getAllowedRoles()
    {
        $returnValue = [];
        $returnValue = [TaoRoles::BACK_OFFICE => new core_kernel_classes_Resource(TaoRoles::BACK_OFFICE)];
        return (array)$returnValue;
    }

    public function getDefaultRole()
    {
        return new core_kernel_classes_Resource(TaoRoles::BACK_OFFICE);
    }

    /**
     * Short description of method logout
     *
     * @access public
     * @return boolean
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function logout()
    {
        $returnValue = (bool)false;
        $returnValue = $this->generisUserService->logout();
        return (bool)$returnValue;
    }

    /**
     * Short description of method getAllUsers
     *
     * @access public
     * @param array $options
     * @param array $filters
     * @return array
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function getAllUsers($options = [], $filters = [GenerisRdf::PROPERTY_USER_LOGIN => '*'])
    {
        $userClass = new core_kernel_classes_Class(TaoOntology::CLASS_URI_TAO_USER);
        $options = array_merge(['recursive' => true, 'like' => true], $options);
        return (array)$userClass->searchInstances($filters, $options);
    }

    /**
     * Returns count of instances, that match conditions in options and filters
     * @access public
     * @param array $options
     * @param array $filters
     * @return int
     * @author Ivan Klimchuk <klimchuk@1pt.com>
     */
    public function getCountUsers($options = [], $filters = [])
    {
        $userClass = new core_kernel_classes_Class(TaoOntology::CLASS_URI_TAO_USER);
        return $userClass->countInstances($filters, $options);
    }

    /**
     * Short description of method toTree
     *
     * @access public
     * @param core_kernel_classes_Class $clazz
     * @param array $options
     * @return array
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function toTree(core_kernel_classes_Class $clazz, array $options = [])
    {
        $returnValue = [];
        $users = $this->getAllUsers(['order' => GenerisRdf::PROPERTY_USER_LOGIN]);
        foreach ($users as $user) {
            $login = (string)$user->getOnePropertyValue(
                new core_kernel_classes_Property(GenerisRdf::PROPERTY_USER_LOGIN)
            );
            $returnValue[] = [
                'data' => tao_helpers_Display::textCutter($login, 16),
                'attributes' => [
                    'id' => tao_helpers_Uri::encode($user->getUri()),
                    'class' => 'node-instance'
                ]
            ];
        }

        return (array)$returnValue;
    }

    /**
     * Add a new user.
     *
     * @param string login The login to give the user.
     * @param string password the password in clear.
     * @param core_kernel_classes_Resource role A role to grant to the user.
     * @param core_kernel_classes_Class A specific class to use to instantiate the new user. If not specified, the class returned by the getUserClass method is used.
     * @return core_kernel_classes_Resource the new user
     * @throws core_kernel_users_Exception If an error occurs.
     */
    public function addUser(
        $login,
        $password,
        core_kernel_classes_Resource $role = null,
        core_kernel_classes_Class $class = null
    ) {
        $this->checkCurrentUserAccess($role);
        if (empty($class)) {
            $class = $this->getRootClass();
        }

        $user = $this->generisUserService->addUser($login, $password, $role, $class);
        //set up default properties
        if (!is_null($user)) {
            $user->setPropertyValue(
                new core_kernel_classes_Property(TaoOntology::PROPERTY_USER_FIRST_TIME),
                GenerisRdf::GENERIS_TRUE
            );
        }

        return $user;
    }

    /**
     * Indicates if a user session is currently opened or not.
     *
     * @return boolean True if a session is opened, false otherwise.
     */
    public function isASessionOpened()
    {
        return common_user_auth_Service::singleton()->isASessionOpened();
    }

    /**
     * Indicates if a given user has a given password.
     *
     * @param string password The password to check.
     * @param core_kernel_classes_Resource user The user you want to check the password.
     * @return boolean
     */
    public function isPasswordValid($password, core_kernel_classes_Resource $user)
    {
        return $this->generisUserService->isPasswordValid($password, $user);
    }

    /**
     * Change the password of a given user.
     *
     * @param core_kernel_classes_Resource user The user you want to change the password.
     * @param string password The md5 hash of the new password.
     */
    public function setPassword(core_kernel_classes_Resource $user, $password)
    {
        return $this->generisUserService->setPassword($user, $password);
    }

    /**
     * Get the roles of a given user.
     *
     * @param core_kernel_classes_Resource $user The user you want to retrieve the roles.
     * @return array An array of core_kernel_classes_Resource.
     */
    public function getUserRoles(core_kernel_classes_Resource $user)
    {
        return $this->generisUserService->getUserRoles($user);
    }

    /**
     * Indicates if a user is granted with a set of Roles.
     *
     * @access public
     * @param Resource user The User instance you want to check Roles.
     * @param roles Can be either a single Resource or an array of Resource that are instances of Role.
     * @return boolean
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function userHasRoles(core_kernel_classes_Resource $user, $roles)
    {
        return $this->generisUserService->userHasRoles($user, $roles);
    }

    /**
     * Attach a Generis Role to a given TAO User. A UserException will be
     * if an error occurs. If the User already has the role, nothing happens.
     *
     * @access public
     * @param Resource user The User you want to attach a Role.
     * @param Resource role A Role to attach to a User.
     * @throws core_kernel_users_Exception If an error occurs.
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function attachRole(core_kernel_classes_Resource $user, core_kernel_classes_Resource $role)
    {
        // check that current user has rights to set this role
        $this->checkCurrentUserAccess($role);
        $this->generisUserService->attachRole($user, $role);
    }

    /**
     * Unnatach a Role from a given TAO User.
     *
     * @access public
     * @param Resource user A TAO user from which you want to unnattach the Role.
     * @param Resource role The Role you want to Unnatach from the TAO User.
     * @throws core_kernel_users_Exception If an error occurs.
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function unnatachRole(core_kernel_classes_Resource $user, core_kernel_classes_Resource $role)
    {
        try {
            $this->checkCurrentUserAccess($role);
            $this->generisUserService->unnatachRole($user, $role);
        } catch (common_exception_Error $e) {
        }
    }

    /**
     * @param core_kernel_classes_Resource $user
     * @param array $properties
     */
    public function attachProperties(core_kernel_classes_Resource $user, array $properties)
    {
        if (array_key_exists(OntologyRdfs::RDFS_LABEL, $properties)) {
            $label = $properties[OntologyRdfs::RDFS_LABEL];
            unset($properties[OntologyRdfs::RDFS_LABEL]);
            $user->setLabel($label);
        }
        $user->setPropertiesValues($properties);
    }

    /**
     * Get the class to use to instantiate users.
     *
     * @return core_kernel_classes_Class The user class.
     */
    public function getRootClass()
    {
        return new core_kernel_classes_Class(TaoOntology::CLASS_URI_TAO_USER);
    }

    /**
     * @param core_kernel_classes_Class $clazz
     * @param string $label
     * @return core_kernel_classes_Resource
     * @throws common_exception_Error
     */
    public function createInstance(core_kernel_classes_Class $clazz, $label = '')
    {
        $user = $this->traitCreateInstance($clazz, $label);
        $this->getEventManager()->trigger(new UserCreatedEvent($user));
        return $user;
    }

    /**
     * Filter roles to leave only permitted roles
     * @param $user
     * @param $roles
     * @return array
     */
    public function getPermittedRoles(core_kernel_classes_Resource $user, array $roles, $encoded = true)
    {
        $exclude = [];
        if (!$this->userHasRoles($user, TaoRoles::SYSTEM_ADMINISTRATOR)) {
            $exclude[] = $encoded ? tao_helpers_Uri::encode(
                TaoRoles::SYSTEM_ADMINISTRATOR
            ) : TaoRoles::SYSTEM_ADMINISTRATOR;
            if (!$this->userHasRoles($user, TaoRoles::GLOBAL_MANAGER)) {
                $exclude[] = $encoded ? tao_helpers_Uri::encode(TaoRoles::GLOBAL_MANAGER) : TaoRoles::GLOBAL_MANAGER;
            }
        }
        if (count($exclude)) {
            $roles = array_filter(
                $roles,
                function ($k) use ($exclude) {
                    return !in_array($k, $exclude);
                },
                ARRAY_FILTER_USE_KEY
            );
        }
        return $roles;
    }

    /**
     * Thrown an exception if user doesn't have permissions
     * @param $roles
     * @throws common_exception_Error
     */
    public function checkCurrentUserAccess($roles)
    {
        if ($this->getCurrentUser() === null) {
            return;
        }
        if ($roles instanceof core_kernel_classes_Resource) {
            $roles = [$roles->getUri()];
        }

        if (is_array($roles)) {
            $roles = array_map(
                function ($role) {
                    return $role instanceof core_kernel_classes_Resource ? $role->getUri() : $role;
                },
                $roles
            );
        }

        if (
            in_array(TaoRoles::SYSTEM_ADMINISTRATOR, $roles)
            && !$this->userHasRoles($this->getCurrentUser(), TaoRoles::SYSTEM_ADMINISTRATOR)
        ) {
            throw new common_exception_Error('Permission denied');
        }

        if (
            in_array(TaoRoles::GLOBAL_MANAGER, $roles)
            && !$this->userHasRoles($this->getCurrentUser(), [TaoRoles::SYSTEM_ADMINISTRATOR, TaoRoles::GLOBAL_MANAGER])
        ) {
            throw new common_exception_Error('Permission denied');
        }
    }

    /**
     * @param core_kernel_classes_Resource $user
     * @param array $values
     * @param string|null $hashForKey
     *
     * @return bool
     * @throws tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function triggerUpdatedEvent(core_kernel_classes_Resource $user, array $values, $hashForKey)
    {
        $triggered = false;
        $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($user);

        if ($binder->bind($values)) {
            if ($hashForKey !== null) {
                $values['hashForKey'] = $hashForKey;
            }

            $this->getEventManager()->trigger(new UserUpdatedEvent($user, $values));
            $triggered = true;
        }

        return $triggered;
    }
}
