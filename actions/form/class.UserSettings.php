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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\oatbox\user\UserTimezoneServiceInterface;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\user\UserSettingsInterface;
use Psr\Container\ContainerInterface;

/**
 * This container initializes the settings form.
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 */
class tao_actions_form_UserSettings extends tao_helpers_form_FormContainer
{
    public const OPTION_LANGUAGE_SERVICE = 'LanguageService';
    public const OPTION_CONTAINER_SERVICE = 'Container';
    public const OPTION_USERTIMEZONE_SERVICE = 'UserTimezoneService';

    /** @var tao_models_classes_LanguageService */
    private $languageService;

    /** @var ContainerInterface */
    private $container;

    /** @var UserTimezoneServiceInterface */
    private $userTimezoneService;

    public function __construct(array $data = [], array $options = [])
    {
        if (
            isset($options[self::OPTION_LANGUAGE_SERVICE])
            && $options[self::OPTION_LANGUAGE_SERVICE] instanceof tao_models_classes_LanguageService
        ) {
            $this->setLanguageService($options[self::OPTION_LANGUAGE_SERVICE]);
        }

        if (
            isset($options[self::OPTION_CONTAINER_SERVICE])
            && $options[self::OPTION_CONTAINER_SERVICE] instanceof ContainerInterface
        ) {
            $this->setContainer($options[self::OPTION_CONTAINER_SERVICE]);
        }

        if (
            isset($options[self::OPTION_USERTIMEZONE_SERVICE])
            && $options[self::OPTION_USERTIMEZONE_SERVICE] instanceof UserTimezoneServiceInterface
        ) {
            $this->setUserTimezoneService($options[self::OPTION_USERTIMEZONE_SERVICE]);
        }

        parent::__construct($data, $options);
    }

    public function setUserTimezoneService(UserTimezoneServiceInterface $userTimezoneService): void
    {
        $this->userTimezoneService = $userTimezoneService;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function setLanguageService(tao_models_classes_LanguageService $languageService): void
    {
        $this->languageService = $languageService;
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
        $userLangService = $this->getContainer()->get(UserLanguageServiceInterface::class);

        // Retrieve languages available for a GUI usage.
        $guiUsage = new core_kernel_classes_Resource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_GUI);
        $guiOptions = [];
        foreach ($langService->getAvailableLanguagesByUsage($guiUsage) as $lang) {
            if ($lang->getLabel()) {
                $guiOptions[tao_helpers_Uri::encode($lang->getUri())] = $lang->getLabel();
            }
        }

        // Retrieve languages available for a Data usage.
        $dataUsage = new core_kernel_classes_Resource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA);
        $dataOptions = [];
        foreach ($langService->getAvailableLanguagesByUsage($dataUsage) as $lang) {
            if ($lang->getLabel()) {
                $dataOptions[tao_helpers_Uri::encode($lang->getUri())] = $lang->getLabel();
            }
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

        if (
            $this->getFeatureFlagChecker()->isEnabled(
                FeatureFlagCheckerInterface::FEATURE_FLAG_SOLAR_DESIGN_ENABLED
            )
        ) {
            $this->addInterfaceModeElement($this->form);
        }
    }

    private function addInterfaceModeElement(tao_helpers_form_Form $form): void
    {
        $interfaceModeElement = tao_helpers_form_FormFactory::getElement(
            UserSettingsInterface::INTERFACE_MODE,
            'Radiobox'
        );
        $interfaceModeElement->setDescription(__('Interface Mode'));
        $interfaceModeElement->setOptions($this->getInterfaceModeOptions());
        $form->addElement($interfaceModeElement);
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
            $this->userTimezoneService = $this->getContainer()->get(
                UserTimezoneServiceInterface::SERVICE_ID
            );
        }

        return $this->userTimezoneService;
    }

    private function getLanguageService(): tao_models_classes_LanguageService
    {
        if (!$this->languageService) {
            $this->languageService = tao_models_classes_LanguageService::singleton();
        }

        return $this->languageService;
    }

    private function getFeatureFlagChecker(): FeatureFlagChecker
    {
        return $this
            ->getContainer()
            ->get(FeatureFlagChecker::class);
    }

    private function getContainer(): ContainerInterface
    {
        if (!$this->container) {
            $this->container = ServiceManager::getServiceManager()->getContainer();
        }

        return $this->container;
    }

    private function getInterfaceModeOptions(): array
    {
        $options = [];
        $property = new core_kernel_classes_Property(GenerisRdf::PROPERTY_USER_INTERFACE_MODE);

        foreach ($property->getRange()->getInstances(true) as $rangeInstance) {
            $options[tao_helpers_Uri::encode($rangeInstance->getUri())] = $rangeInstance->getLabel();
        }

        krsort($options);

        return $options;
    }
}
