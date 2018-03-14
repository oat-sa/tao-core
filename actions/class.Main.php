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
 *               2016-2018 (update and modification) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\GenerisRdf;
use oat\oatbox\event\EventManager;
use oat\oatbox\user\LoginService;
use oat\tao\helpers\TaoCe;
use oat\tao\model\accessControl\ActionResolver;
use oat\tao\model\accessControl\func\AclProxy as FuncProxy;
use oat\tao\model\entryPoint\EntryPointService;
use oat\tao\model\event\LoginFailedEvent;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\event\LogoutSucceedEvent;
use oat\tao\model\menu\MenuService;
use oat\tao\model\menu\Perspective;
use oat\tao\model\mvc\DefaultUrlService;
use oat\tao\model\notification\NotificationInterface;
use oat\tao\model\notification\NotificationServiceInterface;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\model\user\UserLocks;

/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao

 *
 */
class tao_actions_Main extends tao_actions_CommonModule
{

    /**
     * The user service
     *
     * @var tao_models_classes_UserService
     */
    protected $userService;

	/**
	 * Constructor performs initializations actions
	 */
	public function __construct()
	{
		//initialize service
		$this->service = tao_models_classes_TaoService::singleton();
        $this->userService = \tao_models_classes_UserService::singleton();
		$this->defaultData();
	}

	/**
	 * First page, when arriving on a system
	 * to choose front or back office
	 */
    public function entry()
    {
        $entries = array();
        foreach (EntryPointService::getRegistry()->getEntryPoints() as $entry) {
            if (tao_models_classes_accessControl_AclProxy::hasAccessUrl($entry->getUrl())) {
                $entries[] = $entry;
            }
        }

	    if (empty($entries)) {
	        // no access -> error
	        if (common_session_SessionManager::isAnonymous()) {
                /* @var $urlRouteService DefaultUrlService */
                $urlRouteService = $this->getServiceManager()->get(DefaultUrlService::SERVICE_ID);
                $this->redirect($urlRouteService->getLoginUrl());
	        } else {
	            common_session_SessionManager::endSession();
                return $this->returnError(__('You currently have no access to the platform'), true, 403);
	        }
	    } elseif (count($entries) == 1 && !common_session_SessionManager::isAnonymous()) {
	        // single entrypoint -> redirect
	        $entry = current($entries);
	        return $this->redirect($entry->getUrl());
	    } else {
	        // multiple entries -> choice
	        if (!common_session_SessionManager::isAnonymous()) {
	            $this->setData('user', common_session_SessionManager::getSession()->getUserLabel());
	        }
    	    $this->setData('entries', $entries);
            $naviElements = $this->getNavigationElementsByGroup('settings');
            foreach($naviElements as $key => $naviElement) {
                if($naviElement['perspective']->getId() !== 'user_settings') {
                    unset($naviElements[$key]);
                    continue;
                }
            }

            if ($this->hasRequestParameter('errorMessage')){
                $this->setData('errorMessage', $this->getRequestParameter('errorMessage'));
            }
            $this->setData('logout', $this->getServiceManager()->get(DefaultUrlService::SERVICE_ID)->getLogoutUrl());
            $this->setData('userLabel', \common_session_SessionManager::getSession()->getUserLabel());
            $this->setData('settings-menu', $naviElements);
            $this->setData('current-section', $this->getRequestParameter('section'));
            $this->setData('content-template', array('blocks/entry-points.tpl', 'tao'));
            $this->setView('layout.tpl', 'tao');
	    }
	}

    /**
     * Authentication form,
     * default page, main entry point to the user
     * @return void
     * @throws Exception
     * @throws common_ext_ExtensionException
     * @throws core_kernel_persistence_Exception
     */
    public function login()
	{
	    /** @var common_ext_ExtensionsManager $extensionManager */
	    $extensionManager = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
        $extension = $extensionManager->getExtensionById('tao');
        $config = $extension->getConfig('login');

        $disableAutoComplete = !empty($config['disableAutocomplete']);
        $enablePasswordReveal = !empty($config['enablePasswordReveal']);

        $enableIframeProtection = !empty($config['block_iframe_usage']) && $config['block_iframe_usage'];
        if ($enableIframeProtection) {
            \oat\tao\model\security\IFrameBlocker::setHeader();
        }

		$params = array(
            'disableAutocomplete' => $disableAutoComplete,
            'enablePasswordReveal' => $enablePasswordReveal,
        );

		if ($this->hasRequestParameter('redirect')) {
			$redirectUrl = $_REQUEST['redirect'];

			if (substr($redirectUrl, 0,1) == '/' || substr($redirectUrl, 0, strlen(ROOT_URL)) == ROOT_URL) {
				$params['redirect'] = $redirectUrl;
			}
		}

		$container = new tao_actions_form_Login($params);
		$form = $container->getForm();

        if ($form->isSubmited()) {
            if ($form->isValid()) {

                /** @var UserLocks $userLocksService */
                $userLocksService = $this->getServiceLocator()->get(UserLocks::SERVICE_ID);
                /** @var EventManager $eventManager */
                $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);

                try {
                    if ($userLocksService->isLocked($form->getValue('login'))) {
                        common_Logger::i("User '" . $form->getValue('login') . "' has been locked.");

                        $statusDetails = $userLocksService->getStatusDetails($form->getValue('login'));
                        if ($statusDetails['auto']) {
                            $msg = __('You have been locked due to too many failed login attempts. ');
                            if ($userLocksService->getOption(UserLocks::OPTION_USE_HARD_LOCKOUT)) {
                                $msg .= __('Please contact your administrator.');
                            } else {
                                /** @var DateInterval $remaining */
                                $remaining = $statusDetails['remaining'];

                                $reference = new DateTimeImmutable;
                                $endTime = $reference->add($remaining);

                                $diffInSeconds = $endTime->getTimestamp() - $reference->getTimestamp();

                                $msg .= __('Please try in %s.',
                                    $diffInSeconds > 60
                                        ? tao_helpers_Date::displayInterval($statusDetails['remaining'], tao_helpers_Date::FORMAT_INTERVAL_LONG)
                                        : $diffInSeconds . ' ' . ($diffInSeconds == 1 ? __('second') : __('seconds'))
                                );
                            }
                        } else {
                            $msg = __('Your account has been locked, please contact your administrator.');
                        }

                        $this->setData('errorMessage', $msg);
                    } else {
                        if (LoginService::login($form->getValue('login'), $form->getValue('password'))) {
                            $eventManager->trigger(new LoginSucceedEvent($form->getValue('login')));

                            common_Logger::i("Successful login of user '" . $form->getValue('login') . "'.");

                            if ($this->hasRequestParameter('redirect') && tao_models_classes_accessControl_AclProxy::hasAccessUrl($_REQUEST['redirect'])) {
                                $this->redirect($_REQUEST['redirect']);
                            } else {
                                $this->forward('entry');
                            }
                        } else {
                            $eventManager->trigger(new LoginFailedEvent($form->getValue('login')));

                            common_Logger::i("Unsuccessful login of user '" . $form->getValue('login') . "'.");

                            $msg = __('Invalid login or password. Please try again.');

                            if ($userLocksService->getOption(UserLocks::OPTION_USE_HARD_LOCKOUT)) {
                                $remainingAttempts = $userLocksService->getLockoutRemainingAttempts($form->getValue('login'));
                                if ($remainingAttempts !== false) {
                                    if ($remainingAttempts === 0) {
                                        $msg = __('Invalid login or password. Your account has been locked, please contact your administrator.');
                                    } else {
                                        $msg = $msg . ' ' .
                                            ($remainingAttempts === 1
                                                ? __('Last attempt before your account is locked.')
                                                : __('%d attempts left before your account is locked.', $remainingAttempts));
                                    }
                                }
                            }

                            $this->setData('errorMessage', $msg);
                        }
                    }
                } catch (core_kernel_users_Exception $e) {
                    $this->setData('errorMessage', __('Invalid login or password. Please try again.'));
                }
			}
		}

        $renderedForm = $form->render();

        // replace the login form by a fake form that will delegate the submit to the real form
        // this will allow to prevent the browser ability to cache login/password
        if ($disableAutoComplete) {
            // make a copy of the form and replace the form attributes
            $fakeForm = preg_replace('/<form[^>]+>/', '<div class="form loginForm fakeForm">', $renderedForm);
            $fakeForm = str_replace('</form>', '</div>', $fakeForm);

            // replace the password field by a text field in the actual form,
            // so the browser won't detect it and won't be able to cache the credentials
            $renderedForm = preg_replace('/type=[\'"]+password[\'"]+/', 'type="text"', $renderedForm);

            // hide the actual form,
            // it will be submitted through javascript delegation
            $renderedForm = preg_replace_callback('/<form([^>]+)>/', function($matches) {
                $str = $matches[0];
                if (false !== strpos($str, ' style=')) {
                    $str = preg_replace('/ style=([\'"]+)([^\'"]+)([\'"]+)/', ' style=$1$2;display:none;$3', $str);
                } else {
                    $str = '<form' . $matches[1] . ' style="display:none;">';
                }
                return $str;
            }, $renderedForm);

            // the fake form will be displayed instead of the actual form,
            // it will behave like the actual form
            $renderedForm .= $fakeForm;
        }

        $this->setData('form', $renderedForm);
        $this->setData('title', __("TAO Login"));

        $entryPointService = $this->getServiceLocator()->get(EntryPointService::SERVICE_ID);
        $this->setData('entryPoints', $entryPointService->getEntryPoints(EntryPointService::OPTION_PRELOGIN));

        if ($this->hasRequestParameter('msg')) {
            $this->setData('msg', $this->getRequestParameter('msg'));
        }

        $this->setData('content-template', array('blocks/login.tpl', 'tao'));

        $this->setView('layout.tpl', 'tao');
	}

	/**
	 * Logout, destroy the session and back to the login page
	 */
	public function logout()
	{

        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);

        $logins = common_session_SessionManager::getSession()->getUser()->getPropertyValues(GenerisRdf::PROPERTY_USER_LOGIN);
        $eventManager->trigger(new LogoutSucceedEvent(current($logins)));


        common_session_SessionManager::endSession();
                /* @var $urlRouteService DefaultUrlService */
                $urlRouteService = $this->getServiceLocator()->get(DefaultUrlService::SERVICE_ID);

		$this->redirect($urlRouteService->getRedirectUrl('logout'));
	}

	/**
	 * The main action, load the layout
     *
	 * @return void
	 */
    public function index()
    {

        $user      = $this->userService->getCurrentUser();
        $extension = $this->getRequestParameter('ext');
        $structure = $this->getRequestParameter('structure');

		if($this->hasRequestParameter('structure')) {

			// structured mode
			// @todo stop using session to manage uri/classUri
			$this->removeSessionAttribute('uri');
			$this->removeSessionAttribute('classUri');
			$this->removeSessionAttribute('showNodeUri');

            TaoCe::setLastVisitedUrl(
                _url(
                    'index',
                    'Main',
                    'tao',
                    array(
                        'structure' => $structure,
                        'ext'       => $extension
                    )
                )
            );

            $sections = $this->getSections($extension, $structure);
			if (count($sections) > 0) {
				$this->setData('sections', $sections);
			} else {
				common_Logger::w('no sections');
			}
		} else {

            //check if the user is a noob, otherwise redirect him to his last visited extension.
            $firstTime = TaoCe::isFirstTimeInTao();
            if ($firstTime == false) {
               $lastVisited = TaoCe::getLastVisitedUrl();
               if(!is_null($lastVisited)){
                   $this->redirect($lastVisited);
               }
            }
        }

        $perspectiveTypes = array(Perspective::GROUP_DEFAULT, 'settings', 'persistent');
        foreach ($perspectiveTypes as $perspectiveType) {
            $this->setData($perspectiveType . '-menu', $this->getNavigationElementsByGroup($perspectiveType));
        }

        /* @var $notifService NotificationServiceInterface */
        $notifService = $this->getServiceManager()->get(NotificationServiceInterface::SERVICE_ID);

        if($notifService->getVisibility()) {
            $notif = $notifService->notificationCount($user->getUri());

            $this->setData('unread-notification', $notif[NotificationInterface::CREATED_STATUS]);

            $this->setData('notification-url', _url('index' , 'Main' , 'tao' ,
                [
                    'structure' => 'tao_Notifications',
                    'ext'       => 'tao',
                    'section'   => 'settings_my_notifications',
                ]
            ));
        }
        /* @var $urlRouteService DefaultUrlService */
        $urlRouteService = $this->getServiceManager()->get(DefaultUrlService::SERVICE_ID);
        $this->setData('logout', $urlRouteService->getLogoutUrl());

        $this->setData('user_lang', \common_session_SessionManager::getSession()->getDataLanguage());
        $this->setData('userLabel', \common_session_SessionManager::getSession()->getUserLabel());
        // re-added to highlight selected extension in menu
        $this->setData('shownExtension', $extension);
        $this->setData('shownStructure', $structure);

        $this->setData('current-section', $this->getRequestParameter('section'));

        // Add csrf token
        $tokenService = $this->getServiceManager()->get(TokenService::SERVICE_ID);
        $tokenName = $tokenService->getTokenName();
        $token = $tokenService->createToken();
        $this->setCookie($tokenName, $token, null, '/');
        $this->setData('xsrf-token-name', $tokenName);

        //creates the URL of the action used to configure the client side
        $clientConfigParams = array(
            'shownExtension' => $extension,
            'shownStructure' => $structure
        );
        $this->setData('client_config_url', $this->getClientConfigUrl($clientConfigParams));
        $this->setData('content-template', array('blocks/sections.tpl', 'tao'));

		$this->setView('layout.tpl', 'tao');
	}

    /**
     * Get perspective data depending on the group set in structure.xml
     *
     * @param $groupId
     * @return array
     */
    private function getNavigationElementsByGroup($groupId)
    {
        $entries = array();
        foreach (MenuService::getPerspectivesByGroup($groupId) as $i => $perspective) {
            $binding = $perspective->getBinding();
            $children = $this->getMenuElementChildren($perspective);

            if (!empty($binding) || !empty($children)) {
                $entry = array(
                    'perspective' => $perspective,
                    'children'    => $children
                );
                if (!is_null($binding)) {
                    $entry['binding'] = $perspective->getExtension() . '/' . $binding;
                }
                $entries[$i] = $entry;
            }
        }
        return $entries;
    }

    /**
     * Get nested menu elements depending on user rights.
     *
     * @param Perspective $menuElement from the structure.xml
     * @return array menu elements list
     */
    private function getMenuElementChildren(Perspective $menuElement)
    {
        $user = common_Session_SessionManager::getSession()->getUser();
        $children = array();
        foreach ($menuElement->getChildren() as $section) {
            try {
                $resolver = new ActionResolver($section->getUrl());
                if (FuncProxy::accessPossible($user, $resolver->getController(), $resolver->getAction())) {
                    $children[] = $section;
                }
            } catch (ResolverException $e) {
                common_Logger::w('Invalid reference in structures: '.$e->getMessage());
            }
        }
        return $children;
    }

    /**
     * Get the sections of the current extension's structure
     *
     * @param string $shownExtension
     * @param string $shownStructure
     * @return array the sections
     */
    private function getSections($shownExtension, $shownStructure)
    {

        $sections = array();
        $user = common_Session_SessionManager::getSession()->getUser();
        $structure = MenuService::getPerspective($shownExtension, $shownStructure);
        if (!is_null($structure)) {
            foreach ($structure->getChildren() as $section) {

                $resolver = new ActionResolver($section->getUrl());
                if (FuncProxy::accessPossible($user, $resolver->getController(), $resolver->getAction())) {

                    foreach($section->getActions() as $action){
                        $this->getServiceManager()->propagate($action);
                        $resolver = new ActionResolver($action->getUrl());
                        if(!FuncProxy::accessPossible($user, $resolver->getController(), $resolver->getAction())){
                            $section->removeAction($action);
                        }

                    }

    				$sections[] = $section;
                }
            }
        }

        return $sections;
    }


    /**
     * Check if the system is ready
     */
    public function isReady()
    {
		if(tao_helpers_Request::isAjax()){
            // the default ajax response is successful style rastafarai
            $ajaxResponse = new common_AjaxResponse();
        } else {
            throw new common_exception_IsAjaxAction(__CLASS__.'::'.__METHOD__.'()');
        }
    }
}
