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
use oat\oatbox\service\ServiceManager;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\service\ApplicationService;

/**
 * This controller provide the actions to manage the user settings
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 *
 */
class tao_actions_UserSettings extends tao_actions_CommonModule
{

    /**
     * @var tao_models_classes_UserService
     */
    private $userService;

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * Initialize the services
     */
    public function __construct()
    {
        parent::__construct();
        $this->serviceManager = $this->getServiceManager();
        $this->userService = $this->serviceManager->get(tao_models_classes_UserService::SERVICE_ID);
    }

    /**
     * Action dedicated to change the password of the user currently connected.
     */
    public function password()
    {
        $this->setData('formTitle', __('Change password'));
        if ($this->serviceManager->get(ApplicationService::SERVICE_ID)->isDemo()) {
            $this->setData('myForm', __('Unable to change passwords in demo mode'));
        } else {
            $myFormContainer = new tao_actions_form_UserPassword();
            $myForm = $myFormContainer->getForm();
            if ($myForm->isSubmited()) {
                if ($myForm->isValid()) {
                    $user = $this->userService->getCurrentUser();
                    $this->userService->setPassword($user, $myForm->getValue('newpassword'));
                    $this->setData('message', __('Password changed'));
                }
            }
            $this->setData('myForm', $myForm->render());
        }

        $this->setView('form/settings_user.tpl');
    }

    /**
     * Action dedicated to change the settings of the user (language, ...)
     * @throws common_exception_Error
     * @throws tao_models_classes_dataBinding_GenerisFormDataBindingException
     * @throws Exception
     */
    public function properties()
    {
        $myFormContainer = new tao_actions_form_UserSettings($this->getUserSettings());
        $myForm = $myFormContainer->getForm();
        if ($myForm->isSubmited() && $myForm->isValid()) {
            $userLangService = $this->serviceManager->get(UserLanguageServiceInterface::class);

            $currentUser = $this->userService->getCurrentUser();
            $userSettings = [
                GenerisRdf::PROPERTY_USER_TIMEZONE => $myForm->getValue('timezone'),
            ];

            $uiLang = new core_kernel_classes_Resource($myForm->getValue('ui_lang'));
            $userSettings[GenerisRdf::PROPERTY_USER_UILG] = $uiLang->getUri();
            if ($userLangService->isDataLanguageEnabled()) {
                $dataLang 	= new core_kernel_classes_Resource($myForm->getValue('data_lang'));
                $userSettings[GenerisRdf::PROPERTY_USER_DEFLG] = $dataLang->getUri();
            }

            $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($currentUser);

            if ($binder->bind($userSettings)) {

                \common_session_SessionManager::getSession()->refresh();
                $uiLangCode = tao_models_classes_LanguageService::singleton()->getCode($uiLang);
                $extension  = $this->serviceManager->get(common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('tao');
                tao_helpers_I18n::init($extension, $uiLangCode);

                $this->setData('message', __('Settings updated'));

                $this->setData('reload', true);
            }
        }

        $userLabel = $this->userService->getCurrentUser()->getLabel();
        $this->setData('formTitle', __('My settings (%s)', $userLabel));
        $this->setData('myForm', $myForm->render());

        $this->setView('form/settings_user.tpl');
    }

    /**
     * Get the settings of the current user. This method returns an associative array with the following keys:
     *
     * - 'ui_lang': The value associated to this key is a core_kernel_classes_Resource object which represents the language
     * selected for the Graphical User Interface.
     * - 'data_lang': The value associated to this key is a core_kernel_classes_Resource object which represents the language
     * selected to access the data in persistent memory.
     * - 'timezone': The value associated to this key is a core_kernel_classes_Resource object which represents the timezone
     * selected to display times and dates.
     *
     * @return array The URIs of the languages.
     * @throws common_exception_InvalidArgumentType
     */
    private function getUserSettings()
    {
        $currentUser = $this->userService->getCurrentUser();
        $props = $currentUser->getPropertiesValues([
            new core_kernel_classes_Property(GenerisRdf::PROPERTY_USER_UILG),
            new core_kernel_classes_Property(GenerisRdf::PROPERTY_USER_DEFLG),
            new core_kernel_classes_Property(GenerisRdf::PROPERTY_USER_TIMEZONE)
        ]);
        $langSettings = [];
        if (!empty($props[GenerisRdf::PROPERTY_USER_UILG])) {
            $langSettings['ui_lang'] = current($props[GenerisRdf::PROPERTY_USER_UILG])->getUri();
        }
        if (!empty($props[GenerisRdf::PROPERTY_USER_DEFLG])) {
            $langSettings['data_lang'] = current($props[GenerisRdf::PROPERTY_USER_DEFLG])->getUri();
        }

        $langSettings['timezone'] = TIME_ZONE;
        if (!empty($props[GenerisRdf::PROPERTY_USER_TIMEZONE])) {
            $langSettings['timezone'] = (string) current($props[GenerisRdf::PROPERTY_USER_TIMEZONE]);
        }

        return $langSettings;
    }
}
