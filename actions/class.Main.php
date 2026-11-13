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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg
 *                         (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *               2012-2018 (update and modification) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\user\UserRdf;
use oat\oatbox\event\EventManager;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\user\LoginService;
use oat\tao\helpers\TaoCe;
use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\accessControl\ActionResolver;
use oat\tao\model\accessControl\func\AclProxy as FuncProxy;
use oat\tao\model\action\ActionBlackList;
use oat\tao\model\DynamicConfig\DynamicConfigProviderInterface;
use oat\tao\model\entryPoint\EntryPointService;
use oat\tao\model\event\LoginFailedEvent;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\event\LogoutSucceedEvent;
use oat\tao\model\menu\MenuService;
use oat\tao\model\menu\Perspective;
use oat\tao\model\menu\SectionVisibilityByRoleFilter;
use oat\tao\model\menu\SectionVisibilityFilter;
use oat\tao\model\menu\SectionVisibilityFilterInterface;
use oat\tao\model\mvc\DefaultUrlService;
use oat\tao\model\notification\Notification;
use oat\tao\model\notification\NotificationServiceInterface;
use oat\tao\model\session\source\SessionSource;
use oat\tao\model\user\UserLocks;
use tao_helpers_Display as DisplayHelper;

/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 *
 */
class tao_actions_Main extends tao_actions_CommonModule
{
    use LoggerAwareTrait;

    /** @var SectionVisibilityFilterInterface */
    private $sectionVisibilityFilter;

    /**
     * First page, when arriving on a system
     * to choose front or back office
     */
    public function entry()
    {
        $this->defaultData();
        $entries = [];
        foreach (EntryPointService::getRegistry()->getEntryPoints() as $entry) {
            if ($this->isAccessibleDestination($entry->getUrl())) {
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
                $fallback = $this->getDefaultPageUrl();
                if ($fallback !== null) {
                    return $this->redirect($fallback);
                }

                common_session_SessionManager::endSession();
                return $this->returnNoAccess();
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
            $this->setData(
                'portalUrl',
                $this->getDynamicConfigProvider()->getConfigByName(
                    DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME
                )
            );
            $this->setData('settings-menu', $naviElements);
            $this->setData('current-section', $this->getRequestParameter('section'));
            $this->setData('content-template', ['blocks/entry-points.tpl', 'tao']);
            $this->setView('layout.tpl', 'tao');
        }
    }

    public function getDynamicConfigProvider(): DynamicConfigProviderInterface
    {
        return $this->getServiceLocator()->getContainer()->get(DynamicConfigProviderInterface::class);
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

            if ($this->isLocalDestination($redirectUrl)) {
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

                                $humanDiff = $diffInSeconds > 60
                                    ? tao_helpers_Date::displayInterval(
                                        $statusDetails['remaining'],
                                        tao_helpers_Date::FORMAT_INTERVAL_LONG
                                    )
                                    : $diffInSeconds . ' ' . ($diffInSeconds === 1 ? __('second') : __('seconds'));

                                $msg .= __('Please try in %s.', $humanDiff);
                            }
                        } else {
                            $msg = __('Your account has been locked, please contact your administrator.');
                        }

                        $this->setData('errorMessage', $msg);
                    } else {
                        if (LoginService::login($form->getValue('login'), $form->getValue('password'))) {
                            $logins = $this->getSession()->getUser()->getPropertyValues(UserRdf::PROPERTY_LOGIN);

                            $eventManager->trigger(new LoginSucceedEvent(
                                login: current($logins),
                                source: SessionSource::INTERNAL_BACKOFFICE->value
                            ));

                            $this->logInfo("Successful login of user '" . $form->getValue('login') . "'.");

                            if (
                                $this->hasRequestParameter('redirect')
                                && $this->isAccessibleDestination($_REQUEST['redirect'])
                            ) {
                                $this->redirect($_REQUEST['redirect']);
                            } else {
                                $this->forward('entry');
                            }
                        } else {
                            $eventManager->trigger(new LoginFailedEvent($form->getValue('login')));

                            $this->logInfo("Unsuccessful login of user '" . $form->getValue('login') . "'.");

                            $msg = __('Invalid login or password. Please try again.');

                            if ($userLocksService->getOption(UserLocks::OPTION_USE_HARD_LOCKOUT)) {
                                $remainingAttempts = $userLocksService->getLockoutRemainingAttempts(
                                    $form->getValue('login')
                                );
                                if ($remainingAttempts !== false) {
                                    if ($remainingAttempts === 0) {
                                        // phpcs:disable Generic.Files.LineLength
                                        $msg = __('Invalid login or password. Your account has been locked, please contact your administrator.');
                                        // phpcs:enable Generic.Files.LineLength
                                    } else {
                                        $msg .= ' ' . (
                                            $remainingAttempts === 1
                                                // phpcs:disable Generic.Files.LineLength
                                                ? __('Last attempt before your account is locked.')
                                                : __('%d attempts left before your account is locked.', $remainingAttempts)
                                            // phpcs:enable Generic.Files.LineLength
                                        );
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
        $this->setData('hideLogo', $config['hideLogo'] ?? false);

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

        /* @var $urlRouteService DefaultUrlService */
        $urlRouteService = $this->getServiceLocator()->get(DefaultUrlService::SERVICE_ID);

        try {
            $this->redirect((string)$urlRouteService->getRedirectUrl('logout'));
        } finally {
            common_session_SessionManager::endSession();
        }
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
            $section = $this->getRequestParameter('section');
            if (!$this->isAccessiblePerspective($extension, $structure, $section)) {
                return $this->redirectToDefaultPage();
            }

            // structured mode
            // @todo stop using session to manage uri/classUri
            $this->removeSessionAttribute('uri');
            $this->removeSessionAttribute('classUri');
            $this->removeSessionAttribute('showNodeUri');

            $sections = $this->getSections($extension, $structure);

            if (empty($sections)) {
                return $this->redirectToDefaultPage();
            }

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

            $this->setData('sections', $sections);
        } else {
            //check if the user is a noob, otherwise redirect him to his last visited extension.
            $firstTime = TaoCe::isFirstTimeInTao();
            if ($firstTime == false) {
                $lastVisited = TaoCe::getLastVisitedUrl();

                if ($this->isAccessibleDestination($lastVisited, false)) {
                    return $this->redirect($lastVisited);
                }
            }

            return $this->redirectToDefaultPage();
        }

        $this->setData(
            'taoAsATool',
            $this->getDynamicConfigProvider()->hasConfig(DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME)
        );

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
        $this->setData('userLabel', DisplayHelper::htmlEscape($this->getSession()->getUserLabel()));
        $this->setData(
            'portalUrl',
            $this->getDynamicConfigProvider()->getConfigByName(
                DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME
            )
        );
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

        /**
         * @var int $i
         * @var  Perspective $perspective
         */
        foreach (MenuService::getPerspectivesByGroup($groupId) as $i => $perspective) {
            if ($this->isRestricted($perspective)) {
                $this->logDebug(
                    sprintf(
                        "Perspective %s has been ignored base on Section Visibility Filter",
                        $perspective->getId()
                    )
                );
                continue;
            }

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

            //We want to always keep user settings menu dropdown even if there is no children to place logout button.
            if (empty($children) && $perspective->getId() === 'user_settings') {
                $entry = [
                    'perspective' => $perspective,
                    'children'    => []
                ];

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

                if (!$this->getSectionVisibilityFilter()->isVisible($section->getId())) {
                    continue;
                }

                if (
                    !$this->getSectionVisibilityByRoleFilter()->isVisible(
                        $this->getSession()->getUserRoles(),
                        $section->getId()
                    )
                ) {
                    continue;
                }

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
        $sectionVisibilityFilter = $this->getSectionVisibilityFilter();

        if (!is_null($structure)) {
            foreach ($this->getMenuElementChildren($structure) as $section) {
                foreach ($section->getActions() as $action) {
                    $sectionPath = $sectionVisibilityFilter->createSectionPath(
                        [
                            $section->getId(),
                            $action->getId()
                        ]
                    );

                    if (!$sectionVisibilityFilter->isVisible($sectionPath)) {
                        $section->removeAction($action);

                        continue;
                    }

                    $this->propagate($action);
                    $resolver = new ActionResolver($action->getUrl());
                    if (
                        !FuncProxy::accessPossible($user, $resolver->getController(), $resolver->getAction()) ||
                        $this->getServiceLocator()->get(ActionBlackList::SERVICE_ID)->isDisabled($action->getId())
                    ) {
                        $section->removeAction($action);
                    }
                }

                $sections[] = $section;
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

    private function getSectionVisibilityFilter(): SectionVisibilityFilter
    {
        if (empty($this->sectionVisibilityFilter)) {
            $this->sectionVisibilityFilter = $this->getServiceLocator()->get(SectionVisibilityFilter::SERVICE_ID);
        }

        return $this->sectionVisibilityFilter;
    }

    private function getDefaultPageUrl(): ?string
    {
        foreach (MenuService::getPerspectivesByGroup(Perspective::GROUP_DEFAULT) as $perspective) {
            $url = _url('index', 'Main', 'tao', [
                'structure' => $perspective->getId(),
                'ext' => $perspective->getExtension(),
            ]);
            if ($this->isAccessibleDestination($url, false)) {
                return $url;
            }
        }

        return null;
    }

    private function redirectToDefaultPage()
    {
        $url = $this->getDefaultPageUrl();

        return $url === null ? $this->returnNoAccess() : $this->redirect($url);
    }

    private function returnNoAccess()
    {
        $this->response = $this->getPsrResponse()->withStatus(403);

        return $this->returnError(__('You currently have no access to the platform'), false, 403);
    }

    private function isLocalDestination($url): bool
    {
        if (!is_string($url) || $url === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $url)) {
            return false;
        }

        if (strpos($url, ROOT_URL) !== 0 && ($url[0] !== '/' || strpos($url, '//') === 0)) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $rootPath = parse_url(ROOT_URL, PHP_URL_PATH) ?: '/';

        return is_string($path)
            && strpos($path, $rootPath) === 0
            && !preg_match('~(?:^|/)\.{1,2}(?:/|$)|[\x00-\x20\x7f\\\\]~', rawurldecode($path));
    }

    private function isAccessibleDestination($url, bool $allowUnstructured = true): bool
    {
        if (!$this->isLocalDestination($url)) {
            return false;
        }

        try {
            $resolver = new ActionResolver($url);
            $controller = $resolver->getController();
            $action = $resolver->getAction();
            if (
                !method_exists($controller, $action)
                || !AclProxy::hasAccess($this->getSession()->getUser(), $controller, $action, [])
            ) {
                return false;
            }
        } catch (ResolverException $e) {
            return false;
        }

        if (strcasecmp($controller, self::class) !== 0) {
            return true;
        }

        if (strcasecmp($action, 'index') !== 0) {
            return $allowUnstructured;
        }

        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $parameters);
        if (!array_key_exists('structure', $parameters)) {
            // A remembered shell URL would redirect back to itself.
            return $allowUnstructured && !isset($parameters['ext']) && !isset($parameters['section']);
        }

        return $this->isAccessiblePerspective(
            $parameters['ext'] ?? null,
            $parameters['structure'],
            $parameters['section'] ?? null
        );
    }

    private function isAccessiblePerspective($extension, $structure, $section = null): bool
    {
        if (
            !is_string($extension) || $extension === ''
            || !is_string($structure) || $structure === ''
            || ($section !== null && (!is_string($section) || $section === ''))
        ) {
            return false;
        }

        $perspective = MenuService::getPerspective($extension, $structure);
        if (
            $perspective === null || $perspective->getExtension() !== $extension
            || $perspective->getId() !== $structure || $this->isRestricted($perspective)
        ) {
            return false;
        }

        $children = $this->getMenuElementChildren($perspective);
        if ($section === null) {
            return !empty($children);
        }

        foreach ($children as $child) {
            if ($child->getId() === $section) {
                return true;
            }
        }

        return false;
    }

    private function getSectionVisibilityByRoleFilter(): SectionVisibilityByRoleFilter
    {
        return $this->getPsrContainer()->get(SectionVisibilityByRoleFilter::class);
    }

    private function isRestricted(Perspective $perspective)
    {
        return !$this->getSectionVisibilityFilter()->isVisible($perspective->getId())
            || !$this->getSectionVisibilityByRoleFilter()->isVisible(
                $this->getSession()->getUserRoles(),
                $perspective->getId()
            );
    }
}
