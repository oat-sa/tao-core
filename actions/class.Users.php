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
 * 
 */

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\event\EventManagerAwareTrait;
use oat\tao\helpers\ApplicationHelper;
use oat\tao\helpers\UserHelper;
use oat\tao\model\event\UserUpdatedEvent;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\implementation\NoUserLocksService;
use oat\tao\model\user\UserLocks;

/**
 * This controller provide the actions to manage the application users (list/add/edit/delete)
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 *
 */
class tao_actions_Users extends tao_actions_CommonModule
{
    use EventManagerAwareTrait;
    use OntologyAwareTrait;
    /**
     * @var tao_models_classes_UserService
     */
    protected $userService = null;

    /**
     * Constructor performs initializations actions
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->userService = tao_models_classes_UserService::singleton();
        $this->defaultData();

        $extManager = common_ext_ExtensionsManager::singleton();
    }

    /** @return UserLocks */
    public function getUserLocksService()
    {
        return $this->getServiceLocator()->get(UserLocks::SERVICE_ID);
    }

    /**
     * Show the list of users
     * @return void
     */
    public function index()
    {
        $this->setView('user/list.tpl');
    }

    /**
     * Provide the user list data via json
     * @return string|json
     * @throws Exception
     * @throws common_exception_InvalidArgumentType
     */
    public function data()
    {
        $page = $this->getRequestParameter('page');
        $limit = $this->getRequestParameter('rows');
        $sortBy = $this->getRequestParameter('sortby');
        $sortOrder = $this->getRequestParameter('sortorder');
        $filterQuery = $this->getRequestParameter('filterquery');
        $filterColumns = $this->getRequestParameter('filtercolumns');
        $start = $limit * $page - $limit;

        $fieldsMap = [
            'login' => GenerisRdf::PROPERTY_USER_LOGIN,
            'firstname' => GenerisRdf::PROPERTY_USER_FIRSTNAME,
            'lastname' => GenerisRdf::PROPERTY_USER_LASTNAME,
            'email' => GenerisRdf::PROPERTY_USER_MAIL,
            'dataLg' => GenerisRdf::PROPERTY_USER_DEFLG,
            'guiLg' => GenerisRdf::PROPERTY_USER_UILG
        ];

        // sorting
        $order = array_key_exists($sortBy, $fieldsMap) ? $fieldsMap[$sortBy] : $fieldsMap['login'];

        // filtering
        $filters = [
            GenerisRdf::PROPERTY_USER_LOGIN => '*',
        ];
        
        if ($filterQuery) {
            if (!$filterColumns) {
                // if filter columns not set, search by all columns
                $filterColumns = array_keys($fieldsMap);
            }
            $filters = array_flip(array_intersect_key($fieldsMap, array_flip($filterColumns)));
            array_walk($filters, function (&$row, $key) use($filterQuery) {
                $row = $filterQuery;
            });
        }

        $options = array(
            'recursive' => true,
            'like' => true,
            'chaining' => count($filters) > 1 ? 'or' : 'and',
            'order' => $order,
            'orderdir' => strtoupper($sortOrder),
        );

        // get total user count...
        $total = $this->userService->getCountUsers($options, $filters);

        // get the users using requested paging...
        $users = $this->userService->getAllUsers(array_merge($options, [
            'offset' => $start,
            'limit' => $limit
        ]), $filters);

        $rolesProperty = $this->getProperty(GenerisRdf::PROPERTY_USER_ROLES);

        $response = new stdClass();
        $readonly = [];
        $index = 0;

        /** @var core_kernel_classes_Resource $user */
        foreach ($users as $user) {

            $propValues = $user->getPropertiesValues(array(
                GenerisRdf::PROPERTY_USER_LOGIN,
                GenerisRdf::PROPERTY_USER_FIRSTNAME,
                GenerisRdf::PROPERTY_USER_LASTNAME,
                GenerisRdf::PROPERTY_USER_MAIL,
                GenerisRdf::PROPERTY_USER_DEFLG,
                GenerisRdf::PROPERTY_USER_UILG,
                GenerisRdf::PROPERTY_USER_ROLES
            ));

            $roles = $user->getPropertyValues($rolesProperty);
            $labels = [];

            foreach ($roles as $uri) {
                $labels[] = $this->getResource($uri)->getLabel();
            }

            $id = tao_helpers_Uri::encode($user->getUri());
            $login = (string)current($propValues[GenerisRdf::PROPERTY_USER_LOGIN]);
            $firstName = empty($propValues[GenerisRdf::PROPERTY_USER_FIRSTNAME]) ? '' : (string)current($propValues[GenerisRdf::PROPERTY_USER_FIRSTNAME]);
            $lastName = empty($propValues[GenerisRdf::PROPERTY_USER_LASTNAME]) ? '' : (string)current($propValues[GenerisRdf::PROPERTY_USER_LASTNAME]);
            $uiRes = empty($propValues[GenerisRdf::PROPERTY_USER_UILG]) ? null : current($propValues[GenerisRdf::PROPERTY_USER_UILG]);
            $dataRes = empty($propValues[GenerisRdf::PROPERTY_USER_DEFLG]) ? null : current($propValues[GenerisRdf::PROPERTY_USER_DEFLG]);

            $response->data[$index]['id'] = $id;
            $response->data[$index]['login'] = $login;
            $response->data[$index]['firstname'] = $firstName;
            $response->data[$index]['lastname'] = $lastName;
            $response->data[$index]['email'] = (string)current($propValues[GenerisRdf::PROPERTY_USER_MAIL]);
            $response->data[$index]['roles'] = implode(', ', $labels);
            $response->data[$index]['dataLg'] = is_null($dataRes) ? '' : $dataRes->getLabel();
            $response->data[$index]['guiLg'] = is_null($uiRes) ? '' : $uiRes->getLabel();

            $statusInfo = $this->getUserLocksService()->getStatusDetails($login);
            $response->data[$index]['locked'] = $statusInfo['locked'];
            $response->data[$index]['status'] = $statusInfo['status'];
            $response->data[$index]['lockable'] = !$this->getUserLocksService() instanceof NoUserLocksService;

            if ($user->getUri() == LOCAL_NAMESPACE . TaoOntology::DEFAULT_USER_URI_SUFFIX) {
                $readonly[$id] = true;
            }

            $index++;
        }

        $response->page = floor($start / $limit) + 1;
        $response->total = ceil($total / $limit);
        $response->records = count($users);
        $response->readonly = $readonly;

        $this->returnJson($response, 200);
    }

    /**
     * Remove a user
     * The request must contains the user's login to remove
     * @return void
     * @throws Exception
     * @throws common_exception_Error
     */
    public function delete()
    {
        // Csrf token validation
        $tokenService = $this->getServiceLocator()->get(TokenService::SERVICE_ID);
        $tokenName = $tokenService->getTokenName();
        $token = $this->getRequestParameter($tokenName);
        if (! $tokenService->checkToken($token)) {
            \common_Logger::w('Xsrf validation failed');
            $this->returnJson([
                'success' => false,
                'message' => 'Not authorized to perform action'
            ]);
            return;
        } else {
            $tokenService->revokeToken($token);
            $newToken = $tokenService->createToken();
            $this->setCookie($tokenName, $newToken, null, '/');
        }

        $deleted = false;
        $message = __('An error occurred during user deletion');
        if (ApplicationHelper::isDemo()) {
            $message = __('User deletion not permitted on a demo instance');
        } elseif ($this->hasRequestParameter('uri')) {
            $user = new core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
            $this->checkUser($user->getUri());

            if ($this->userService->removeUser($user)) {
                $deleted = true;
                $message = __('User deleted successfully');
            }
        }
        $this->returnJson(array(
            'success' => $deleted,
            'message' => $message
        ));
    }

    /**
     * form to add a user
     * @return void
     * @throws Exception
     * @throws \oat\generis\model\user\PasswordConstraintsException
     * @throws tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function add()
    {
        $container = new tao_actions_form_Users($this->getClass(TaoOntology::CLASS_URI_TAO_USER));
        $form = $container->getForm();

        if ($form->isSubmited()) {
            if ($form->isValid()) {
                $values = $form->getValues();
                $values[GenerisRdf::PROPERTY_USER_PASSWORD] = core_kernel_users_Service::getPasswordHash()->encrypt($values['password1']);
                unset($values['password1']);
                unset($values['password2']);

                $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($container->getUser());

                if ($binder->bind($values)) {
                    $this->setData('message', __('User added'));
                    $this->setData('exit', true);
                }
            }
        }

        $this->setData('loginUri', tao_helpers_Uri::encode(GenerisRdf::PROPERTY_USER_LOGIN));
        $this->setData('formTitle', __('Add a user'));
        $this->setData('myForm', $form->render());
        $this->setView('user/form.tpl');
    }

    /**
     * @throws Exception
     */
    public function addInstanceForm()
    {
        if (!tao_helpers_Request::isAjax()) {
            throw new Exception("wrong request mode");
        }

        $clazz = $this->getClass(TaoOntology::CLASS_URI_TAO_USER);
        $formContainer = new tao_actions_form_CreateInstance(array($clazz), array());
        $form = $formContainer->getForm();

        if ($form->isSubmited()) {
            if ($form->isValid()) {

                $properties = $form->getValues();
                $instance = $this->createInstance(array($clazz), $properties);

                $this->setData('message', __($instance->getLabel() . ' created'));
                $this->setData('selectTreeNode', $instance->getUri());
            }
        }

        $this->setData('formTitle', __('Create instance of ') . $clazz->getLabel());
        $this->setData('myForm', $form->render());

        $this->setView('form.tpl', 'tao');
    }

    /**
     * action used to check if a login can be used
     * @return void
     * @throws Exception
     */
    public function checkLogin()
    {
        if (!tao_helpers_Request::isAjax()) {
            throw new Exception("wrong request mode");
        }

        $data = array('available' => false);
        if ($this->hasRequestParameter('login')) {
            $data['available'] = $this->userService->loginAvailable($this->getRequestParameter('login'));
        }

        $this->returnJson($data);
    }

    /**
     * Form to edit a user
     * User login must be set in parameter
     * @return void
     * @throws Exception
     * @throws \oat\generis\model\user\PasswordConstraintsException
     * @throws common_exception_Error
     * @throws tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function edit()
    {
        $user = $this->handleRequestParams();

        $types = $user->getTypes();
        $myFormContainer = new tao_actions_form_Users(reset($types), $user);
        $myForm = $myFormContainer->getForm();

        if ($myForm->isSubmited()) {
            if ($myForm->isValid()) {
                $values = $myForm->getValues();

                if (!empty($values['password2']) && !empty($values['password3'])) {
                    $values[GenerisRdf::PROPERTY_USER_PASSWORD] = core_kernel_users_Service::getPasswordHash()->encrypt($values['password2']);
                }

                unset($values['password2']);
                unset($values['password3']);

                if (!preg_match("/[A-Z]{2,4}$/", trim($values[GenerisRdf::PROPERTY_USER_UILG]))) {
                    unset($values[GenerisRdf::PROPERTY_USER_UILG]);
                }
                if (!preg_match("/[A-Z]{2,4}$/", trim($values[GenerisRdf::PROPERTY_USER_DEFLG]))) {
                    unset($values[GenerisRdf::PROPERTY_USER_DEFLG]);
                }

                $this->userService->checkCurrentUserAccess($values[GenerisRdf::PROPERTY_USER_ROLES]);

                // leave roles which are not in the allowed list for current user
                $oldRoles = $this->userService->getUserRoles($user);
                $allowedRoles = $this->userService->getPermittedRoles($this->userService->getCurrentUser(), $oldRoles, false);
                $staticRoles = array_diff($oldRoles, $allowedRoles);
                $values[GenerisRdf::PROPERTY_USER_ROLES] = array_merge($values[GenerisRdf::PROPERTY_USER_ROLES], $staticRoles);

                $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($user);

                if ($binder->bind($values)) {
                    $this->getEventManager()->trigger(new UserUpdatedEvent($user, $values));
                    $this->setData('message', __('User saved'));
                }
            }
        }

        $this->setData('formTitle', __('Edit a user'));
        $this->setData('myForm', $myForm->render());
        $this->setView('user/form.tpl');
    }

    /**
     * Removes all locks from user account
     * @throws Exception
     */
    public function unlock()
    {
        $user = $this->handleRequestParams();

        if ($this->getUserLocksService()->unlockUser($user)) {
            $this->returnJson(['success' => true, 'message' => __('User %s successfully unlocked', UserHelper::getUserLogin(UserHelper::getUser($user)))]);
        } else {
            $this->returnJson(['success' => false, 'message' => __('User %s can not be unlocked', UserHelper::getUserLogin(UserHelper::getUser($user)))]);
        }
    }

    /**
     * Locks user account, he can not login in to the system anymore
     * @throws Exception
     */
    public function lock()
    {
        $user = $this->handleRequestParams();

        if ($this->getUserLocksService()->lockUser($user)) {
            $this->returnJson(['success' => true, 'message' => __('User %s successfully locked', UserHelper::getUserLogin(UserHelper::getUser($user)))]);
        } else {
            $this->returnJson(['success' => false, 'message' => __('User %s can not be locked', UserHelper::getUserLogin(UserHelper::getUser($user)))]);
        }
    }

    /**
     * @throws Exception
     * @return core_kernel_classes_Resource
     */
    private function handleRequestParams()
    {
        if (!$this->hasRequestParameter('uri')) {
            throw new Exception('Please set the user uri in request parameter');
        }

        $userUri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
        $this->checkUser($userUri);

        return $this->getResource($userUri);
    }

    /**
     * Check whether user user data can be changed
     * @param $uri
     * @throws Exception
     */
    private function checkUser($uri)
    {
        if ($uri === LOCAL_NAMESPACE . TaoOntology::DEFAULT_USER_URI_SUFFIX) {
            throw new Exception('Default user data cannot be changed');
        }
    }
}
