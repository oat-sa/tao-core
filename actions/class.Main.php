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
 *               2012-2018 (update and modification) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\user\UserRdf;
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
use oat\tao\model\notification\Notification;
use oat\tao\model\notification\NotificationServiceInterface;
use oat\tao\model\user\UserLocks;
use oat\oatbox\log\LoggerAwareTrait;

/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 *
 */
class tao_actions_Main extends tao_actions_CommonModule
{
    use LoggerAwareTrait;

    /**
     * First page, when arriving on a system
     * to choose front or back office
     */
    public function entry()
    {
        $this->defaultData();
        $entries = [];
        foreach (EntryPointService::getRegistry()->getEntryPoints() as $entry) {
            if (tao_models_classes_accessControl_AclProxy::hasAccessUrl($entry->getUrl())) {
                $entries[] = $entry;
            }
        }

        if (empty($entries)) {
            // no access -> error
            if (common_session_SessionManager::isAnonymous()) {
                /* @var $urlRouteService DefaultUrlService */
                $urlRouteService = $this->getServiceLocator()->get(DefaultUrlService::SERVICE_ID);
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
                $this->setData('user', $this->getSession()->getUserLabel());
            }
            $this->setData('entries', $entries);
            $naviElements = $this->getNavigationElementsByGroup('settings');
            foreach ($naviElements as $key => $naviElement) {
                if ($naviElement['perspective']->getId() !== 'user_settings') {
                    unset($naviElements[$key]);
                    continue;
                }
            }

            if ($this->hasRequestParameter('errorMessage')) {
                $this->setData('errorMessage', $this->getRequestParameter('errorMessage'));
            }
            $this->setData('logout', $this->getServiceLocator()->get(DefaultUrlService::SERVICE_ID)->getLogoutUrl());
            $this->setData('userLabel', $this->getSession()->getUserLabel());
            $this->setData('settings-menu', $naviElements);
            $this->setData('current-section', $this->getRequestParameter('section'));
            $this->setData('content-template', ['blocks/entry-points.tpl', 'tao']);
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
        $this->defaultData();
        /** @var common_ext_ExtensionsManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
        $extension = $extensionManager->getExtensionById('tao');
        $config = $extension->getConfig('login');

        $disableAutoComplete = !empty($config['disableAutocomplete']);
        $enablePasswordReveal = !empty($config['enablePasswordReveal']);
        $disableAutofocus = !empty($config['disableAutofocus']);

        $enableIframeProtection = !empty($config['block_iframe_usage']) && $config['block_iframe_usage'];
        if ($enableIframeProtection) {
            \oat\tao\model\security\IFrameBlocker::setHeader();
        }

        $params = [
            'disableAutocomplete' => $disableAutoComplete,
            'enablePasswordReveal' => $enablePasswordReveal,
        ];

        if ($this->hasRequestParameter('redirect')) {
            $redirectUrl = $_REQUEST['redirect'];

            if (substr($redirectUrl, 0, 1) == '/' || substr($redirectUrl, 0, strlen(ROOT_URL)) == ROOT_URL) {
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
                        $this->logInfo("User '" . $form->getValue('login') . "' has been locked.");

                        $statusDetails = $userLocksService->getStatusDetails($form->getValue('login'));
                        if ($statusDetails['auto']) {
                            $msg = __('You have been locked due to too many failed login attempts. ');
                            if ($userLocksService->getOption(UserLocks::OPTION_USE_HARD_LOCKOUT)) {
                                $msg .= __('Please contact your administrator.');
                            } else {
                                /** @var DateInterval $remaining */
                                $remaining = $statusDetails['remaining'];

                                $reference = new DateTimeImmutable();
                                $endTime = $reference->add($remaining);

                                $diffInSeconds = $endTime->getTimestamp() - $reference->getTimestamp();

                                $msg .= __(
                                    'Please try in %s.',
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
                            $logins = $this->getSession()->getUser()->getPropertyValues(UserRdf::PROPERTY_LOGIN);

                            $eventManager->trigger(new LoginSucceedEvent(current($logins)));

                            $this->logInfo("Successful login of user '" . $form->getValue('login') . "'.");

                            if ($this->hasRequestParameter('redirect') && tao_models_classes_accessControl_AclProxy::hasAccessUrl($_REQUEST['redirect'])) {
                                $this->redirect($_REQUEST['redirect']);
                            } else {
                                $this->forward('entry');
                            }
                        } else {
                            $eventManager->trigger(new LoginFailedEvent($form->getValue('login')));

                            $this->logInfo("Unsuccessful login of user '" . $form->getValue('login') . "'.");

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
            } else {
                foreach ($form->getElements() as $formElement) {
                    $fieldError = $formElement->getError();
                    if ($fieldError) {
                        $this->setData('fieldMessages_' . $formElement->getName(), $fieldError);
                    }
                }
            }
        }

        $this->setData('title', __("TAO Login"));

        $this->setData('autocompleteDisabled', (int)$disableAutoComplete);
        $this->setData('passwordRevealEnabled', (int)$enablePasswordReveal);
        $this->setData('autofocusDisabled', (int)$disableAutofocus);

        $entryPointService = $this->getServiceLocator()->get(EntryPointService::SERVICE_ID);
        $this->setData('entryPoints', $entryPointService->getEntryPoints(EntryPointService::OPTION_PRELOGIN));

        if ($this->hasRequestParameter('msg')) {
            $this->setData('msg', $this->getRequestParameter('msg'));
        }

        $this->setData('show_gdpr', !empty($config['show_gdpr']) && $config['show_gdpr']);

        $this->setData('content-template', 'login');

        $this->setView('layout.tpl', 'tao');
    }

    /**
     * Logout, destroy the session and back to the login page
     */
    public function logout()
    {
        $this->defaultData();
        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);

        $logins = $this->getSession()->getUser()->getPropertyValues(UserRdf::PROPERTY_LOGIN);
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
        $this->defaultData();
        $user      = $this->getUserService()->getCurrentUser();
        $extension = $this->getRequestParameter('ext');
        $structure = $this->getRequestParameter('structure');

        if ($this->hasRequestParameter('structure')) {
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
                    [
                        'structure' => $structure,
                        'ext'       => $extension
                    ]
                )
            );

            $sections = $this->getSections($extension, $structure);
            if (count($sections) > 0) {
                $this->setData('sections', $sections);
            } else {
                $this->logWarning('no sections');
            }
        } else {
            //check if the user is a noob, otherwise redirect him to his last visited extension.
            $firstTime = TaoCe::isFirstTimeInTao();
            if ($firstTime == false) {
                $lastVisited = TaoCe::getLastVisitedUrl();

                if (!is_null($lastVisited)) {
                    $this->redirect($lastVisited);
                }
            }
        }

        $perspectiveTypes = [Perspective::GROUP_DEFAULT, 'settings', 'persistent'];
        foreach ($perspectiveTypes as $perspectiveType) {
            $this->setData($perspectiveType . '-menu', $this->getNavigationElementsByGroup($perspectiveType));
        }

        /* @var $notifService NotificationServiceInterface */
        $notifService = $this->getServiceLocator()->get(NotificationServiceInterface::SERVICE_ID);

        if ($notifService->getVisibility()) {
            $notif = $notifService->notificationCount($user->getUri());

            $this->setData('unread-notification', $notif[Notification::CREATED_STATUS]);

            $this->setData('notification-url', _url(
                'index',
                'Main',
                'tao',
                [
                    'structure' => 'tao_Notifications',
                    'ext'       => 'tao',
                    'section'   => 'settings_my_notifications',
                ]
            ));
        }
        /* @var $urlRouteService DefaultUrlService */
        $urlRouteService = $this->getServiceLocator()->get(DefaultUrlService::SERVICE_ID);
        $this->setData('logout', $urlRouteService->getLogoutUrl());

        $this->setData('user_lang', $this->getSession()->getDataLanguage());
        $this->setData('userLabel', $this->getSession()->getUserLabel());
        // re-added to highlight selected extension in menu
        $this->setData('shownExtension', $extension);
        $this->setData('shownStructure', $structure);

        $this->setData('current-section', $this->getRequestParameter('section'));

        //creates the URL of the action used to configure the client side
        $clientConfigParams = [
            'shownExtension' => $extension,
            'shownStructure' => $structure
        ];
        $this->setData('client_config_url', $this->getClientConfigUrl($clientConfigParams));
        $this->setData('content-template', ['blocks/sections.tpl', 'tao']);

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
        $entries = [];
        foreach (MenuService::getPerspectivesByGroup($groupId) as $i => $perspective) {
            $binding = $perspective->getBinding();
            $children = $this->getMenuElementChildren($perspective);

            if (!empty($binding) || !empty($children)) {
                $entry = [
                    'perspective' => $perspective,
                    'children'    => $children
                ];
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
        $user = $this->getSession()->getUser();
        $children = [];
        foreach ($menuElement->getChildren() as $section) {
            try {
                $resolver = new ActionResolver($section->getUrl());
                if (FuncProxy::accessPossible($user, $resolver->getController(), $resolver->getAction())) {
                    $children[] = $section;
                }
            } catch (ResolverException $e) {
                $this->logWarning('Invalid reference in structures: ' . $e->getMessage());
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

        $sections = [];
        $user = $this->getSession()->getUser();
        $structure = MenuService::getPerspective($shownExtension, $shownStructure);
        if (!is_null($structure)) {
            foreach ($structure->getChildren() as $section) {
                $resolver = new ActionResolver($section->getUrl());
                if (FuncProxy::accessPossible($user, $resolver->getController(), $resolver->getAction())) {
                    foreach ($section->getActions() as $action) {
                        $this->propagate($action);
                        $resolver = new ActionResolver($action->getUrl());
                        if (!FuncProxy::accessPossible($user, $resolver->getController(), $resolver->getAction())) {
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
        if ($this->isXmlHttpRequest()) {
            // the default ajax response is successful style rastafarai
            $ajaxResponse = new common_AjaxResponse();
        } else {
            throw new common_exception_IsAjaxAction(__CLASS__ . '::' . __METHOD__ . '()');
        }
    }

    /**
     * @return tao_models_classes_UserService
     */
    protected function getUserService()
    {
        return $this->getServiceLocator()->get(tao_models_classes_UserService::SERVICE_ID);
    }
}
