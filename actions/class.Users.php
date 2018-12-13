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
 *               2013-2018 (update and modification) Open Assessment Technologies SA;
 *
 */

use oat\generis\Helper\UserHashForEncryption;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\event\EventManagerAwareTrait;
use oat\tao\helpers\ApplicationHelper;
use oat\tao\helpers\UserHelper;
use oat\tao\model\event\UserUpdatedEvent;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\UserLocks;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\oatbox\log\LoggerAwareTrait;

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
    use LoggerAwareTrait;

    /**
     * Show the list of users
     * @return void
     */
    public function index()
    {
        $this->defaultData();
        $userLangService = $this->getServiceLocator()->get(UserLanguageServiceInterface::class);
        $this->setData('user-data-lang-enabled', $userLangService->isDataLanguageEnabled());
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
        $userService = $this->getServiceLocator()->get(tao_models_classes_UserService::class);
        $userLangService = $this->getServiceLocator()->get(UserLanguageServiceInterface::class);
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
            'guiLg' => GenerisRdf::PROPERTY_USER_UILG,
            'roles' => GenerisRdf::PROPERTY_USER_ROLES
        ];
        if ($userLangService->isDataLanguageEnabled()) {
            $fieldsMap['dataLg'] = GenerisRdf::PROPERTY_USER_DEFLG;
        }

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
        $total = $userService->getCountUsers($options, $filters);

        // get the users using requested paging...
        $users = $userService->getAllUsers(array_merge($options, [
            'offset' => $start,
            'limit' => $limit
        ]), $filters);

        $rolesProperty = $this->getProperty(GenerisRdf::PROPERTY_USER_ROLES);

        $response = new stdClass();
        $readonly = [];
        $index = 0;

        /** @var core_kernel_classes_Resource $user */
        foreach ($users as $user) {

            $propValues = $user->getPropertiesValues(array_values($fieldsMap));

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
            if ($userLangService->isDataLanguageEnabled()) {
                $dataRes = empty($propValues[GenerisRdf::PROPERTY_USER_DEFLG]) ? null : current($propValues[GenerisRdf::PROPERTY_USER_DEFLG]);
                $response->data[$index]['dataLg'] = is_null($dataRes) ? '' : $dataRes->getLabel();
            }

            $response->data[$index]['id'] = $id;
            $response->data[$index]['login'] = $login;
            $response->data[$index]['firstname'] = $firstName;
            $response->data[$index]['lastname'] = $lastName;
            $response->data[$index]['email'] = (string)current($propValues[GenerisRdf::PROPERTY_USER_MAIL]);
            $response->data[$index]['roles'] = implode(', ', $labels);
            $response->data[$index]['guiLg'] = is_null($uiRes) ? '' : $uiRes->getLabel();

            $statusInfo = $this->getUserLocksService()->getStatusDetails($login);
            $response->data[$index]['lockable'] = $statusInfo['lockable'];
            $response->data[$index]['locked'] = $statusInfo['locked'];
            $response->data[$index]['status'] = $statusInfo['status'];

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
        $userService = $this->getServiceLocator()->get(tao_models_classes_UserService::class);
        // Csrf token validation
        $tokenService = $this->getServiceLocator()->get(TokenService::SERVICE_ID);
        $tokenName = $tokenService->getTokenName();
        $token = $this->getRequestParameter($tokenName);
        if (! $tokenService->checkToken($token)) {
            $this->logWarning('Xsrf validation failed');
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
            $user = $this->getResource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
            $this->checkUser($user->getUri());

            if ($userService->removeUser($user)) {
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
        $this->defaultData();
        $container = new tao_actions_form_Users($this->getClass(TaoOntology::CLASS_URI_TAO_USER));
        $form = $container->getForm();

        if ($form->isSubmited()) {
            if ($form->isValid()) {
                $values = $form->getValues();
                $values[GenerisRdf::PROPERTY_USER_PASSWORD] = core_kernel_users_Service::getPasswordHash()->encrypt($values['password1']);
                $plainPassword = $values['password1'];
                unset($values['password1']);
                unset($values['password2']);

                $user = $container->getUser();
                $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($container->getUser());

                if ($binder->bind($values)) {
                    $this->getEventManager()->trigger(new UserUpdatedEvent(
                            $user,
                            array_merge($values, ['hashForKey' => UserHashForEncryption::hash($plainPassword)]))
                    );
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
     * @throws common_exception_BadRequest
     */
    public function addInstanceForm()
    {
        $this->defaultData();
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        $clazz = $this->getClass(TaoOntology::CLASS_URI_TAO_USER);
        $formContainer = new tao_actions_form_CreateInstance(array($clazz), array());
        $form = $formContainer->getForm();

        if ($form->isSubmited()) {
            if ($form->isValid()) {

                $properties = $form->getValues();
                $instance = $this->createInstance(array($clazz), $properties);

                $this->setData('message', __('%s created', $instance->getLabel()));
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
     * @throws common_exception_BadRequest
     */
    public function checkLogin()
    {
        $this->defaultData();
        $userService = $this->getServiceLocator()->get(tao_models_classes_UserService::class);
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        $data = array('available' => false);
        if ($this->hasRequestParameter('login')) {
            $data['available'] = $userService->loginAvailable($this->getRequestParameter('login'));
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
        $this->defaultData();
        $userService = $this->getServiceLocator()->get(tao_models_classes_UserService::class);
        $user = $this->getUserResource();

        $types = $user->getTypes();
        $myFormContainer = new tao_actions_form_Users(reset($types), $user);
        $myForm = $myFormContainer->getForm();

        if ($myForm->isSubmited()) {
            if ($myForm->isValid()) {
                $values = $myForm->getValues();
                if (!empty($values['password2']) && !empty($values['password3'])) {
                    $plainPassword =  $values['password2'];
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

                $userService->checkCurrentUserAccess($values[GenerisRdf::PROPERTY_USER_ROLES]);

                // leave roles which are not in the allowed list for current user
                $oldRoles = $userService->getUserRoles($user);
                $allowedRoles = $userService->getPermittedRoles($userService->getCurrentUser(), $oldRoles, false);
                $staticRoles = array_diff($oldRoles, $allowedRoles);
                $values[GenerisRdf::PROPERTY_USER_ROLES] = array_merge($values[GenerisRdf::PROPERTY_USER_ROLES], $staticRoles);

                $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($user);

                if ($binder->bind($values)) {
                    $data = [];
                    if (isset($plainPassword)){
                        $data = ['hashForKey' => UserHashForEncryption::hash($plainPassword)];
                    }
                    $this->getEventManager()->trigger(new UserUpdatedEvent(
                        $user,
                        array_merge($values, $data))
                    );
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
        $user = UserHelper::getUser($this->getUserResource());

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
        $user = UserHelper::getUser($this->getUserResource());

        if ($this->getUserLocksService()->lockUser($user)) {
            $this->returnJson(['success' => true, 'message' => __('User %s successfully locked', UserHelper::getUserLogin(UserHelper::getUser($user)))]);
        } else {
            $this->returnJson(['success' => false, 'message' => __('User %s can not be locked', UserHelper::getUserLogin(UserHelper::getUser($user)))]);
        }
    }

    /**
     * @throws common_exception_MissingParameter
     * @throws Exception
     * @return core_kernel_classes_Resource
     */
    private function getUserResource()
    {
        if (!$this->hasRequestParameter('uri')) {
            throw new common_exception_MissingParameter('uri');
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

    /**
     * @return UserLocks
     */
    protected function getUserLocksService()
    {
        return $this->getServiceLocator()->get(UserLocks::SERVICE_ID);
    }

}
