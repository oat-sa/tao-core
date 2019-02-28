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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

use oat\generis\model\OntologyAwareTrait;
use oat\tao\helpers\form\validators\CspHeaderValidator;

/**
 * Class tao_actions_form_CspHeader
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class tao_actions_form_CspHeader extends tao_helpers_form_FormContainer
{

    use OntologyAwareTrait;

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
        $this->form = new tao_helpers_form_xhtml_Form('cspHeader');

        $this->form->setDecorators([
            'element'			=> new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div']),
            'group'				=> new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div', 'cssClass' => 'form-group']),
            'error'				=> new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div', 'cssClass' => 'form-error ui-state-error ui-corner-all']),
            'actions-bottom'	=> new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div', 'cssClass' => 'form-toolbar'])
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
        $this->sourceDomainsElement->addClass('hidden');
        $this->sourceDomainsElement->setDescription(
            "Each domain should be added on a new line. \n
            Valid domain formats: www.example.com, *.example.com, http://www.example.com"
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
            'all'  => __('Allow for all domains'),
            'list' => __('Allow for the following domains'),
        ];
    }

    /**
     * Set the data received through a POST request
     */
    private function setFormData()
    {
        $currentConfig = $this->getConfiguration();
        if (isset($_POST[self::SOURCE_RADIO_NAME]) && array_key_exists($_POST[self::SOURCE_RADIO_NAME], $this->getSourceOptions())) {
            $this->sourceElement->setValue($_POST[self::SOURCE_RADIO_NAME]);
        }

        if (isset($_POST[self::SOURCE_LIST_NAME])) {
            $this->sourceDomainsElement->setValue($_POST[self::SOURCE_LIST_NAME]);
        }
    }

    /**
     * Set the validation needed for the form elements.
     */
    private function setValidation()
    {
        $this->sourceDomainsElement->addValidator(new CspHeaderValidator());
        $this->sourceElement->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
    }

    /**
     * Get the current configuration
     */
    public function getConfiguration()
    {
        $headerConfig = $this->getClass(self::CSP_HEADER_CONFIG);
        $config = $headerConfig->getInstances();
        if ($headerConfig->exists() === false) {
            $headerConfig->createInstance();
        }

    }

    /**
     * Stores the configuration based on the form values.
     */
    public function saveConfiguration()
    {
        $formValues = $this->getForm()->getValues();

        $configValue = $formValues[self::SOURCE_RADIO_NAME];
        if ($configValue === 'all') {
            $configValue = '*';
        }

        if ($configValue === 'list') {
            $sources = trim(str_replace("\r", '', $formValues[self::SOURCE_LIST_NAME]));
            $configValue = explode("\n", $sources);
        }


    }
}
