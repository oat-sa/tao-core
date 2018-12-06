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
 *				 2013-2018 (update and modification) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
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
    use OntologyAwareTrait;

    /**
     * Action dedicated to change the password of the user currently connected.
     */
    public function password()
    {
        $this->setData('formTitle', __("Change password"));
        if ($this->getServiceLocator()->get(ApplicationService::SERVICE_ID)->isDemo()) {
            $this->setData('myForm', __('Unable to change passwords in demo mode'));
        } else {
            $myFormContainer = new tao_actions_form_UserPassword();
            $myForm = $myFormContainer->getForm();
            if($myForm->isSubmited()){
                if($myForm->isValid()){
                    $user = $this->getUserService()->getCurrentUser();
                    $this->getServiceLocator()->get(tao_models_classes_UserService::SERVICE_ID)
                        ->setPassword($user, $myForm->getValue('newpassword'));
                    $this->setData('message', __('Password changed'));
                }
            }
            $this->setData('myForm', $myForm->render());
        }

        $this->setView('form/settings_user.tpl');
    }

    /**
     * Action dedicated to change the settings of the user (language, ...)
     */
    public function properties()
    {
        $myFormContainer = new tao_actions_form_UserSettings($this->getUserSettings());
        $myForm = $myFormContainer->getForm();
        if ($myForm->isSubmited() && $myForm->isValid()) {
            $userLangService = $this->getServiceLocator()->get(UserLanguageServiceInterface::class);

            $currentUser = $this->getUserService()->getCurrentUser();
            $userSettings = [
                GenerisRdf::PROPERTY_USER_TIMEZONE => $myForm->getValue('timezone'),
            ];

            $uiLang = $this->getResource($myForm->getValue('ui_lang'));
            $userSettings[GenerisRdf::PROPERTY_USER_UILG] = $uiLang->getUri();
            if ($userLangService->isDataLanguageEnabled()) {
                $dataLang = $this->getResource($myForm->getValue('data_lang'));
                $userSettings[GenerisRdf::PROPERTY_USER_DEFLG] = $dataLang->getUri();
            }

            $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($currentUser);

            if($binder->bind($userSettings)){

                $this->getSession()->refresh();
                $uiLangCode		= tao_models_classes_LanguageService::singleton()->getCode($uiLang);
                $extension      = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('tao');
                tao_helpers_I18n::init($extension, $uiLangCode);

                $this->setData('message', __('Settings updated'));

                $this->setData('reload', true);
            }
        }
        $userLabel = $this->getUserService()->getCurrentUser()->getLabel();
        $this->setData('formTitle', __("My settings (%s)", $userLabel));
        $this->setData('myForm', $myForm->render());

        //$this->setView('form.tpl');
        $this->setView('form/settings_user.tpl');
    }



    /**
     * Get the settings of the current user. This method returns an associative array with the following keys:
     *
     * - 'ui_lang': The value associated to this key is a core_kernel_classes_Resource object which represents the language
     * selected for the Graphical User Interface.
     * - 'data_lang': The value associated to this key is a core_kernel_classes_Resource object which respresents the language
     * selected to access the data in persistent memory.
     * - 'timezone': The value associated to this key is a core_kernel_classes_Resource object which respresents the timezone
     * selected to display times and dates.
     *
     * @return array The URIs of the languages.
     */
    private function getUserSettings(){
        $currentUser = $this->getUserService()->getCurrentUser();
        $props = $currentUser->getPropertiesValues([
            $this->getProperty(GenerisRdf::PROPERTY_USER_UILG),
            $this->getProperty(GenerisRdf::PROPERTY_USER_DEFLG),
            $this->getProperty(GenerisRdf::PROPERTY_USER_TIMEZONE)
        ]);
        $langs = [];
        if (!empty($props[GenerisRdf::PROPERTY_USER_UILG])) {
            $langs['ui_lang'] = current($props[GenerisRdf::PROPERTY_USER_UILG])->getUri();
        }
        if (!empty($props[GenerisRdf::PROPERTY_USER_DEFLG])) {
            $langs['data_lang'] = current($props[GenerisRdf::PROPERTY_USER_DEFLG])->getUri();
        }
        $langs['timezone'] = !empty($props[GenerisRdf::PROPERTY_USER_TIMEZONE])
            ? (string) current($props[GenerisRdf::PROPERTY_USER_TIMEZONE])
            : TIME_ZONE;
        return $langs;
    }

    /**
     * @return tao_models_classes_UserService
     */
    protected function getUserService()
    {
        return $this->getServiceLocator()->get(tao_models_classes_UserService::SERVICE_ID);
    }
}
