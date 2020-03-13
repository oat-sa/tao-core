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
use oat\tao\model\security\Business\Domain\SettingsCollection;

/**
 * Handling of the CSP Header form
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class tao_actions_form_CspHeader extends tao_helpers_form_FormContainer
{
    use ServiceManagerAwareTrait;

    public const SETTINGS_DATA = 'settings';

    private const SOURCE_RADIO_NAME       = 'iframeSourceOption';
    private const SOURCE_LIST_NAME        = 'iframeSourceDomains';
    private const FORCED_TLS_NAME         = 'isTlsForced';
    private const FORCED_TLS_ELEMENT_NAME = self::FORCED_TLS_NAME . '_0';

    /** @var tao_helpers_form_elements_xhtml_Radiobox */
    private $sourceElement;

    /** @var tao_helpers_form_elements_xhtml_Textarea */
    private $sourceDomainsElement;

    /** @var tao_helpers_form_elements_xhtml_Checkbox */
    private $forcedTlsElement;

    /** @var SettingsCollection */
    private $settings;

    /**
     * @inheritdoc
     */
    public function initForm()
    {
        $this->settings = $this->data[self::SETTINGS_DATA];
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
        $this->forcedTlsElement = tao_helpers_form_FormFactory::getElement(self::FORCED_TLS_NAME, 'Checkbox');

        $this->setValidation();
        $this->sourceElement->setOptions($this->getSourceOptions());
        $this->forcedTlsElement->setOptions([1 => __('Force HTTPS on this platform')]);

        $this->initializeFormData();

        $this->form->addElement($this->sourceElement);
        $this->form->addElement($this->sourceDomainsElement);
        $this->form->addElement($this->forcedTlsElement);

        $this->groupElements();

        $this->form->setActions(tao_helpers_form_FormFactory::getCommonActions());
    }

    public function getSettings(): SettingsCollection
    {
        $this->handleFormPost();

        return $this->settings;
    }

    /**
     * @return array
     */
    private function getSourceOptions(): array
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
    private function handleFormPost(): void
    {
        $postData = $this->getPostData();

        if (isset($postData[self::SOURCE_RADIO_NAME]) && array_key_exists($postData[self::SOURCE_RADIO_NAME], $this->getSourceOptions())) {
            $this->settings->findContentSecurityPolicy()->setValue($postData[self::SOURCE_RADIO_NAME]);
        }

        if (isset($postData[self::SOURCE_LIST_NAME])) {
            $this->settings->findContentSecurityPolicyWhitelist()->setValue($postData[self::SOURCE_LIST_NAME]);
        }

        $this->settings->findTransportSecurity()->setValue((string)!empty($postData[self::FORCED_TLS_ELEMENT_NAME]));
    }

    private function groupElements(): void
    {
        $this->form->createGroup(
            'sources',
            '<h3>' . __('Sources that can embed this platform in an iFrame') . '</h3>',
            [self::SOURCE_RADIO_NAME, self::SOURCE_LIST_NAME]
        );
        $this->form->createGroup(
            'tls',
            sprintf('<h3>%s</h3>', __('Transport Layer Security')),
            [self::FORCED_TLS_NAME]
        );
    }

    private function initializeFormData(): void
    {
        $this->sourceElement->setValue(
            $this->settings->findContentSecurityPolicy()->getValue()
        );
        $this->sourceDomainsElement->setValue(
            $this->settings->findContentSecurityPolicyWhitelist()->getValue()
        );
        $this->forcedTlsElement->setValue(
            $this->settings->findTransportSecurity()->getValue()
        );
    }

    /**
     * Set the validation needed for the form elements.
     */
    private function setValidation(): void
    {
        $this->sourceDomainsElement->addValidator(new CspHeaderValidator(['sourceElement' => $this->sourceElement]));
        $this->sourceElement->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
    }
}
