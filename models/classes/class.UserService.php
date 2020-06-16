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
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2014 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\user\LoginService;
use oat\oatbox\user\User;
use oat\tao\model\event\UserCreatedEvent;
use oat\tao\model\event\UserRemovedEvent;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\TaoRoles;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\event\UserUpdatedEvent;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\ClassServiceTrait;
use oat\tao\model\GenerisServiceTrait;
use core_kernel_classes_Resource as Resource;
use core_kernel_classes_Class as TypeClass;

/**
 * This class provide service on user management
 */
class tao_models_classes_UserService extends ConfigurableService implements core_kernel_users_UsersManagement
{
    use ClassServiceTrait;

    use GenerisServiceTrait {
        createInstance as protected traitCreateInstance;
    }

    public const SERVICE_ID = 'tao/UserService';

    public const OPTION_ALLOW_API = 'allow_api';

    /**
     * the core user service
     *
     * @var    core_kernel_users_Service
     */
    protected $generisUserService;

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
     *
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
     * @param string login
     * @param string password
     *
     * @return     boolean
     * @author     Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @deprecated
     */
    public function loginUser($login, $password)
    {
        $returnValue = false;
        try {
            $returnValue = LoginService::login($login, $password);
        } catch (core_kernel_users_Exception $ue) {
            common_Logger::e('A fatal error occured at user login time: ' . $ue->getMessage());
        }

        return $returnValue;
    }


    /**
     * retrieve the logged in user
     *
     * @return Resource
     * @throws common_exception_Error
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function getCurrentUser()
    {
        $returnValue = null;
        if (!common_session_SessionManager::isAnonymous()) {
            $userUri = \common_session_SessionManager::getSession()->getUser()->getIdentifier();
            if (!empty($userUri)) {
                $returnValue = new Resource($userUri);
            } else {
                common_Logger::d('no userUri');
            }
        }

        return $returnValue;
    }


    /**
     * Check if the login is already used
     *
     * @param string login
     * @param TypeClass|null $class
     *
     * @return boolean
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function loginExists($login, TypeClass $class = null)
    {
        return $this->generisUserService->loginExists($login, $class) ?? false;
    }

    /**
     * Check if the login is available (because it's unique)
     *
     * @param string login
     *
     * @return boolean
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function loginAvailable($login)
    {
        $returnValue = false;

        if (!empty($login)) {
            $returnValue = !$this->loginExists($login);
        }

        return $returnValue;
    }


    /**
     * Get a user that has a given login.
     *
     * @param string login the user login is the unique identifier to retrieve him.
     * @param TypeClass A specific class to search the user.
     *
     * @return Resource
     * @throws common_exception_InvalidArgumentType
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function getOneUser($login, TypeClass $class = null)
    {
        $returnValue = null;
        if (empty($login)) {
            throw new common_exception_InvalidArgumentType('Missing login for ' . __FUNCTION__);
        }

        $user = $this->generisUserService->getOneUser($login, $class ?? $this->getRootClass());

        if ($user !== null) {
            $allowedRoles = $this->getAllowedRoles();
            if ($this->generisUserService->userHasRoles($user, $allowedRoles)) {
                $returnValue = $user;
            } else {
                common_Logger::i(sprintf('User found for login \'%s\' but does not have matching roles', $login));
            }
        } else {
            common_Logger::i(sprintf('No user found for login \'%s\'', $login));
        }

        return $returnValue;
    }

    /**
     * @param  $userId
     *
     * @return User
     * @throws common_exception_Error
     */
    public function getUserById($userId)
    {
        if (is_string($userId)) {
            $userId = new Resource($userId);
        }

        if ($userId instanceof Resource) {
            $userId = new core_kernel_users_GenerisUser($userId);
        }

        if (!($userId instanceof core_kernel_users_GenerisUser)) {
            common_Logger::i('Unable to get user from ' . $userId);
            $userId = null;
        }

        return $userId;
    }


    /**
     * Remove a user
     *
     * @param Resource $user
     *
     * @return boolean
     * @throws common_exception_Error
     * @author       Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function removeUser(Resource $user)
    {
        $returnValue = false;

        if (!is_null($user)) {
            $this->checkCurrentUserAccess($this->getUserRoles($user));
            $returnValue = $this->generisUserService->removeUser($user);
            $this->getEventManager()->trigger(new UserRemovedEvent($user->getUri()));
        }

        return $returnValue;
    }

    /**
     * Returns a list of all concrete roles (instances of GenerisRdf::CLASS_ROLE)
     * which are allowed to login
     *
     * @return array
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function getAllowedRoles()
    {
        return [TaoRoles::BACK_OFFICE => $this->getDefaultRole()];
    }

    public function getDefaultRole()
    {
        return new Resource(TaoRoles::BACK_OFFICE);
    }

    /**
     * Short description of method logout
     *
     * @return boolean
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function logout()
    {
        return $this->generisUserService->logout();
    }

    /**
     * Short description of method getAllUsers
     *
     * @param array $options
     * @param array $filters
     *
     * @return array
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function getAllUsers($options = [], $filters = [GenerisRdf::PROPERTY_USER_LOGIN => '*'])
    {
        $userClass = new TypeClass(TaoOntology::CLASS_URI_TAO_USER);
        $options = array_merge(['recursive' => true, 'like' => true], $options);

        return $userClass->searchInstances($filters, $options);
    }


    /**
     * Returns count of instances, that match conditions in options and filters
     *
     * @param array $options
     * @param array $filters
     *
     * @return integer
     * @author Ivan Klimchuk <klimchuk@1pt.com>
     */
    public function getCountUsers($options = [], $filters = [])
    {
        $userClass = new TypeClass(TaoOntology::CLASS_URI_TAO_USER);

        return $userClass->countInstances($filters, $options);
    }

    /**
     * Short description of method toTree
     *
     * @param TypeClass $clazz
     * @param array     $options
     *
     * @return array
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public function toTree(TypeClass $clazz, array $options = [])
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
                    'class' => 'node-instance',
                ],
            ];
        }

        return $returnValue;
    }

    /**
     * Add a new user.
     *
     * @param string login The login to give the user.
     * @param string password the password in clear.
     * @param Resource role A role to grant to the user.
     * @param TypeClass A specific class to use to instantiate the new user.
     * If not specified, the class returned by the getUserClass method is used.
     *
     * @return Resource the new user
     * @throws core_kernel_users_Exception If an error occurs.
     * @throws common_exception_Error
     */
    public function addUser(
        $login,
        $password,
        Resource $role = null,
        TypeClass $class = null
    ) {
        $this->checkCurrentUserAccess($role);

        if ($class === null) {
            $class = $this->getRootClass();
        }

        $user = $this->generisUserService->addUser($login, $password, $role, $class);
        // set up default properties
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
     * @param Resource user The user you want to check the password.
     *
     * @return boolean
     * @throws core_kernel_users_Exception
     */
    public function isPasswordValid($password, Resource $user)
    {
        return $this->generisUserService->isPasswordValid($password, $user);
    }

    /**
     * Change the password of a given user.
     *
     * @param Resource $user
     * @param Resource user The user you want to change the password.
     *
     * @throws core_kernel_users_Exception
     */
    public function setPassword(Resource $user, $password)
    {
        $this->generisUserService->setPassword($user, $password);
    }

    /**
     * Get the roles of a given user.
     *
     * @param Resource $user The user you want to retrieve the roles.
     *
     * @return array An array of Resource.
     */
    public function getUserRoles(Resource $user)
    {
        return $this->generisUserService->getUserRoles($user);
    }

    /**
     * Indicates if a user is granted with a set of Roles.
     *
     * @param Resource $user
     * @param Resource user The User instance you want to check Roles.
     *
     * @return boolean
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function userHasRoles(Resource $user, $roles)
    {
        return $this->generisUserService->userHasRoles($user, $roles);
    }

    /**
     * Attach a Generis Role to a given TAO User. A UserException will be
     * if an error occurs. If the User already has the role, nothing happens.
     *
     * @param Resource $user
     * @param Resource $role
     *
     * @throws common_exception_Error
     * @throws core_kernel_users_Exception If an error occurs.*@throws common_exception_Error
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function attachRole(Resource $user, Resource $role)
    {
        // check that current user has rights to set this role
        $this->checkCurrentUserAccess($role);
        $this->generisUserService->attachRole($user, $role);
    }

    /**
     * Un-attach a Role from a given TAO User.
     *
     * @param Resource $user
     * @param Resource $role
     *
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function unnatachRole(Resource $user, Resource $role)
    {
        try {
            $this->checkCurrentUserAccess($role);
            $this->generisUserService->unnatachRole($user, $role);
        } catch (common_exception_Error $e) {
        }
    }

    /**
     * @param Resource $user
     * @param array    $properties
     */
    public function attachProperties(Resource $user, array $properties)
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
     * @return TypeClass The user class.
     */
    public function getRootClass()
    {
        return new TypeClass(TaoOntology::CLASS_URI_TAO_USER);
    }

    /**
     * @param TypeClass $clazz
     * @param string    $label
     *
     * @return Resource
     * @throws common_exception_Error
     */
    public function createInstance(TypeClass $clazz, $label = '')
    {
        $user = $this->traitCreateInstance($clazz, $label);
        $this->getEventManager()->trigger(new UserCreatedEvent($user));
        return $user;
    }

    /**
     * Filter roles to leave only permitted roles
     *
     * @param Resource $user
     * @param array    $roles
     * @param bool     $encoded
     *
     * @return array
     */
    public function getPermittedRoles(Resource $user, array $roles, $encoded = true)
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
     *
     * @param  $roles
     *
     * @throws common_exception_Error
     */
    public function checkCurrentUserAccess($roles)
    {
        if ($this->getCurrentUser() === null) {
            return;
        }

        if ($roles instanceof Resource) {
            $roles = [$roles->getUri()];
        }

        if (is_array($roles)) {
            $roles = array_map(
                function ($role) {
                    return $role instanceof Resource ? $role->getUri() : $role;
                },
                $roles
            );
        }

        if (
            in_array(TaoRoles::SYSTEM_ADMINISTRATOR, $roles, true)
            && !$this->userHasRoles($this->getCurrentUser(), TaoRoles::SYSTEM_ADMINISTRATOR)
        ) {
            throw new common_exception_Error('Permission denied');
        }

        if (
            in_array(TaoRoles::GLOBAL_MANAGER, $roles, true)
            && !$this->userHasRoles($this->getCurrentUser(), [TaoRoles::SYSTEM_ADMINISTRATOR, TaoRoles::GLOBAL_MANAGER])
        ) {
            throw new common_exception_Error('Permission denied');
        }
    }

    /**
     * @param Resource    $user
     * @param array       $values
     * @param string|null $hashForKey
     *
     * @return boolean
     * @throws tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function triggerUpdatedEvent(Resource $user, array $values, $hashForKey)
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
