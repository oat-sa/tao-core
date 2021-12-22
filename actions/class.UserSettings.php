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
 *               2013-2021 (update and modification) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\service\ApplicationService;
use oat\tao\model\user\UserSettingsFormFactory;
use oat\tao\model\user\UserSettingsServiceInterface;
use oat\tao\model\user\implementation\UserSettingsService;
use tao_helpers_form_FormContainer as FormContainer;
use tao_helpers_Display as DisplayHelper;

/**
 * This controller provides actions to manage the user settings
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 */
class tao_actions_UserSettings extends tao_actions_CommonModule
{
    use OntologyAwareTrait;

    /**
     * Action dedicated to change the password of the user currently connected.
     */
    public function password()
    {
        $this->setData('formTitle', __("Change password"));

        if ($this->isDemoMode()) {
            $this->setData('myForm', __('Unable to change passwords in demo mode'));
        } else {
            $passwordFormContainer = new tao_actions_form_UserPassword([], [FormContainer::CSRF_PROTECTION_OPTION => true]);
            $passwordForm = $passwordFormContainer->getForm();

            if ($passwordForm->isSubmited() && $passwordForm->isValid()) {
                $newPassword = $passwordForm->getValue('newpassword');
                $userService = $this->getUserService();
                $userService->setPassword($userService->getCurrentUser(), $newPassword);

                $this->setData('message', __('Password changed'));
            }

            $this->setData('myForm', $passwordForm->render());
        }

        $this->setView('form/settings_user.tpl');
    }

    /**
     * Action dedicated to change the settings of the user (language, ...)
     */
    public function properties()
    {
        $userLangService = $this->getUserLanguageService();
        $currentUser = $this->getUserService()->getCurrentUser();

        $settingsForm = $this->getUserSettingsFormFactory()->create(
            $this->getUserSettingsService()->get($currentUser),
            $this->getLanguageService()->getLanguageByCode(
                $userLangService->getDefaultLanguage()
            ),
            [
                UserSettingsFormFactory::PARAM_USE_CSRF_PROTECTION => true
            ]
        );

        if ($settingsForm->isSubmited() && $settingsForm->isValid()) {
            $userSettingsData = [
                GenerisRdf::PROPERTY_USER_TIMEZONE => $settingsForm->getValue('timezone'),
            ];

            $uiLang = $this->getResource($settingsForm->getValue('ui_lang'));
            $userSettingsData[GenerisRdf::PROPERTY_USER_UILG] = $uiLang->getUri();

            if ($userLangService->isDataLanguageEnabled()) {
                $dataLang = $this->getResource($settingsForm->getValue('data_lang'));
                $userSettingsData[GenerisRdf::PROPERTY_USER_DEFLG] = $dataLang->getUri();
            }

            $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($currentUser);

            if ($binder->bind($userSettingsData)) {
                $this->getSession()->refresh();

                $uiLangCode = tao_models_classes_LanguageService::singleton()->getCode($uiLang);
                $extension = $this->getExtensionManager()->getExtensionById('tao');
                tao_helpers_I18n::init($extension, $uiLangCode);

                $this->setData('message', __('Settings updated'));
                $this->setData('reload', true);
            }
        }

        $this->setData(
            'formTitle',
            __('My settings (%s)', DisplayHelper::htmlEscape($currentUser->getLabel()))
        );

        $this->setData('myForm', $settingsForm->render());

        $this->setView('form/settings_user.tpl');
    }

    private function getExtensionManager(): common_ext_ExtensionsManager
    {
        return $this->getPsrContainer()->get(common_ext_ExtensionsManager::SERVICE_ID);
    }

    private function getUserService(): tao_models_classes_UserService
    {
        return $this->getPsrContainer()->get(tao_models_classes_UserService::class);
    }

    private function getUserSettingsService(): UserSettingsServiceInterface
    {
        return $this->getPsrContainer()->get(UserSettingsService::class);
    }

    private function getUserSettingsFormFactory(): UserSettingsFormFactory
    {
        return $this->getPsrContainer()->get(UserSettingsFormFactory::class);
    }

    private function getUserLanguageService(): UserLanguageServiceInterface
    {
        return $this->getPsrContainer()->get(UserLanguageServiceInterface::class);
    }

    private function getLanguageService(): tao_models_classes_LanguageService
    {
        return tao_models_classes_LanguageService::singleton();
    }

    private function isDemoMode(): bool
    {
        return $this->getPsrContainer()->get(ApplicationService::SERVICE_ID)->isDemo();
    }
}
