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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

use oat\oatbox\service\ServiceManager;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\oatbox\user\UserTimezoneServiceInterface;

/**
 * This container initializes the settings form.
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 */
class tao_actions_form_UserSettings extends tao_helpers_form_FormContainer
{
    /** @var tao_models_classes_LanguageService */
    private $languageService;

    /** @var ServiceManager */
    private $serviceManager;

    /** @var UserTimezoneServiceInterface */
    private $userTimezoneService;

    public function __construct(array $data = [], array $options = [])
    {
        if (isset($options['LanguageService'])) {
            $this->setLanguageService($options['LanguageService']);
        }

        if (isset($options['ServiceManager'])) {
            $this->setServiceManager($options['ServiceManager']);
        }

        if (isset($options['UserTimezoneService'])) {
            $this->setUserTimezoneService($options['UserTimezoneService']);
        }

        parent::__construct($data, $options);
    }

    /**
     * @inheritdoc
     * @throws common_Exception
     * @throws Exception
     */
    protected function initForm()
    {
        $this->form = tao_helpers_form_FormFactory::getForm('settings');

        $actions = tao_helpers_form_FormFactory::getCommonActions('top');
        $this->form->setActions([], 'top');
        $this->form->setActions($actions);
    }

    /**
     * @inheritdoc
     * @throws common_Exception
     * @throws common_exception_Error
     */
    protected function initElements()
    {
        $langService = $this->getLanguageService();
        $userLangService = $this->getServiceManager()->get(UserLanguageServiceInterface::class);

        // Retrieve languages available for a GUI usage.
        $guiUsage = new core_kernel_classes_Resource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_GUI);
        $guiOptions = [];
        foreach ($langService->getAvailableLanguagesByUsage($guiUsage) as $lang) {
            $guiOptions[tao_helpers_Uri::encode($lang->getUri())] = $lang->getLabel();
        }

        // Retrieve languages available for a Data usage.
        $dataUsage = new core_kernel_classes_Resource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA);
        $dataOptions = [];
        foreach ($langService->getAvailableLanguagesByUsage($dataUsage) as $lang) {
            $dataOptions[tao_helpers_Uri::encode($lang->getUri())] = $lang->getLabel();
        }

        $uiLangElement = tao_helpers_form_FormFactory::getElement('ui_lang', 'Combobox');
        $uiLangElement->setDescription(__('Interface language'));
        $uiLangElement->setOptions($guiOptions);

        $this->form->addElement($uiLangElement);

        if ($userLangService->isDataLanguageEnabled()) {
            $dataLangElement = tao_helpers_form_FormFactory::getElement('data_lang', 'Combobox');
            $dataLangElement->setDescription(__('Data language'));
            $dataLangElement->setOptions($dataOptions);
            $this->form->addElement($dataLangElement);
        }

        $this->addTimezoneEl($this->form);
    }

    private function addTimezoneEl($form): void
    {
        if ($this->getUserTimezoneService()->isUserTimezoneEnabled()) {
            $tzElement = tao_helpers_form_FormFactory::getElement('timezone', 'Combobox');
            $tzElement->setDescription(__('Time zone'));

            $options = [];
            foreach (DateTimeZone::listIdentifiers() as $id) {
                $options[$id] = $id;
            }
            $tzElement->setOptions($options);

            $form->addElement($tzElement);
        }
    }

    private function getUserTimezoneService(): UserTimezoneServiceInterface
    {
        if (!$this->userTimezoneService) {
            $this->userTimezoneService = $this->getServiceManager()->get(
                UserTimezoneServiceInterface::SERVICE_ID
            );
        }

        return $this->userTimezoneService;
    }

    public function setUserTimezoneService(UserTimezoneServiceInterface $userTimezoneService): void
    {
        $this->userTimezoneService = $userTimezoneService;
    }

    private function getLanguageService(): tao_models_classes_LanguageService
    {
        if (!$this->languageService) {
            $this->languageService = tao_models_classes_LanguageService::singleton();
        }

        return $this->languageService;
    }

    public function setLanguageService(tao_models_classes_LanguageService $languageService): void
    {
        $this->languageService = $languageService;
    }

    private function getServiceManager(): ServiceManager
    {
        if (!$this->serviceManager) {
            $this->serviceManager = ServiceManager::getServiceManager();
        }

        return $this->serviceManager;
    }

    public function setServiceManager(ServiceManager $serviceManager): void
    {
        $this->serviceManager = $serviceManager;
    }
}
