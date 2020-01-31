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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\tao\helpers\form\validators\CspHeaderValidator;
use oat\tao\model\service\SettingsStorage;
use oat\tao\model\settings\CspHeaderSettingsInterface;

/**
 * Handling of the CSP Header form
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class tao_actions_form_CspHeader extends tao_helpers_form_FormContainer
{
    use ServiceManagerAwareTrait;

    const SOURCE_RADIO_NAME = 'iframeSourceOption';
    const SOURCE_LIST_NAME  = 'iframeSourceDomains';

    /**
     * @var \tao_helpers_form_elements_xhtml_Radiobox
     */
    private $sourceElement;

    /**
     * @var \tao_helpers_form_elements_xhtml_Textarea
     */
    private $sourceDomainsElement;

    /**
     * @inheritdoc
     */
    public function initForm()
    {
        $this->setServiceLocator($this->data['serviceLocator']);
        $this->form = new tao_helpers_form_xhtml_Form('cspHeader');

        $this->form->setDecorators([
            'element'           => new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div']),
            'group'             => new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div', 'cssClass' => 'form-group']),
            'error'             => new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div', 'cssClass' => 'form-error ui-state-error ui-corner-all hidden']),
            'actions-bottom'    => new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div', 'cssClass' => 'form-toolbar'])
        ]);
    }

    /**
     * @inheritdoc
     */
    public function initElements()
    {
        $this->sourceElement = tao_helpers_form_FormFactory::getElement(self::SOURCE_RADIO_NAME, 'Radiobox');
        $this->sourceDomainsElement = tao_helpers_form_FormFactory::getElement(self::SOURCE_LIST_NAME, 'Textarea');
        $this->sourceDomainsElement->setAttribute('rows', 10);
        $this->sourceDomainsElement->setHelp(
            "<div class='help-text'>Each domain should be added on a new line. \n
            Valid domain formats: www.example.com, *.example.com, http://www.example.com</div>"
        );

        $this->setValidation();
        $this->sourceElement->setOptions($this->getSourceOptions());

        $this->setFormData();

        $this->form->addElement($this->sourceElement);
        $this->form->addElement($this->sourceDomainsElement);

        $this->form->createGroup(
            'sources',
            '<h3>' . __('Sources that can embed this platform in an iFrame') . '</h3>',
            [self::SOURCE_RADIO_NAME, self::SOURCE_LIST_NAME]
        );

        $this->form->setActions(tao_helpers_form_FormFactory::getCommonActions());
    }

    /**
     * @return array
     */
    private function getSourceOptions()
    {
        return [
            'none' => __('Forbid for all domains'),
            '*'  => __('Allow for all domains'),
            'self'  => __('Only allow for my own domain (%s)', ROOT_URL),
            'list' => __('Allow for the following domains'),
        ];
    }

    /**
     * Set the form data based on the available data
     */
    private function setFormData()
    {
        $postData = $this->getPostData();
        $currentSetting = $this->getSettings();
        $listSettings = [];

        if ($currentSetting === 'list') {
            $listSettings = $this->getListSettings();
        }

        if ($currentSetting && !isset($postData[self::SOURCE_RADIO_NAME])) {
            $this->sourceElement->setValue($currentSetting);
        }

        if (!empty($listSettings) && !isset($postData[self::SOURCE_LIST_NAME])) {
            $this->sourceDomainsElement->setValue(implode("\n", $listSettings));
        }

        if (isset($postData[self::SOURCE_RADIO_NAME]) && array_key_exists($postData[self::SOURCE_RADIO_NAME], $this->getSourceOptions())) {
            $this->sourceElement->setValue($postData[self::SOURCE_RADIO_NAME]);
        }

        if (isset($postData[self::SOURCE_LIST_NAME])) {
            $this->sourceDomainsElement->setValue($postData[self::SOURCE_LIST_NAME]);
        }
    }

    /**
     * Set the validation needed for the form elements.
     */
    private function setValidation()
    {
        $this->sourceDomainsElement->addValidator(new CspHeaderValidator(['sourceElement' => $this->sourceElement]));
        $this->sourceElement->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
    }

    /**
     * Get the current settings
     */
    private function getSettings()
    {
        $settingsStorage = $this->getSettingsStorage();
        if (!$settingsStorage->exists(CspHeaderSettingsInterface::CSP_HEADER_SETTING)) {
            return '';
        }

        return $settingsStorage->get(CspHeaderSettingsInterface::CSP_HEADER_SETTING);
    }

    /**
     * Get the current list settings
     */
    private function getListSettings()
    {
        $settingsStorage = $this->getSettingsStorage();
        if (!$settingsStorage->exists(CspHeaderSettingsInterface::CSP_HEADER_LIST)) {
            return [];
        }

        return json_decode($settingsStorage->get(CspHeaderSettingsInterface::CSP_HEADER_LIST));
    }

    /**
     * Stores the settings based on the form values.
     */
    public function saveSettings()
    {
        $formValues = $this->getForm()->getValues();
        $settingStorage = $this->getSettingsStorage();

        $configValue = $formValues[self::SOURCE_RADIO_NAME];
        if ($configValue === 'list') {
            $sources = trim(str_replace("\r", '', $formValues[self::SOURCE_LIST_NAME]));
            $sources = explode("\n", $sources);
            $settingStorage->set(CspHeaderSettingsInterface::CSP_HEADER_LIST, json_encode($sources));
        }

        $settingStorage->set(CspHeaderSettingsInterface::CSP_HEADER_SETTING, $configValue);
    }

    /**
     * Get the SettingsStorage service
     * @return SettingsStorage
     */
    private function getSettingsStorage()
    {
        return $this->getServiceLocator()->get(SettingsStorage::SERVICE_ID);
    }
}
