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

use oat\tao\helpers\form\validators\CspHeaderValidator;

/**
 * Class tao_actions_form_CspHeader
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class tao_actions_form_CspHeader extends tao_helpers_form_FormContainer
{

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
        $this->sourceElement = tao_helpers_form_FormFactory::getElement('iframeSourceOption', 'Radiobox');
        $this->sourceDomainsElement = tao_helpers_form_FormFactory::getElement('iframeSourceDomains', 'Textarea');
        $this->sourceDomainsElement->setAttribute('rows', 10);
        $this->sourceDomainsElement->addClass('hidden');
        $this->sourceDomainsElement->setDescription(
            "Each domain should be added on a new line. \n
            Valid domain formats: www.example.com, *.example.com, http://www.example.com"
        );

        $this->setValidation();
        $this->sourceElement->setOptions($this->getSourceOptions());

        $this->setPostData();

        $this->form->addElement($this->sourceElement);
        $this->form->addElement($this->sourceDomainsElement);

        $this->form->createGroup(
            'sources',
            '<h3>' . __('Sources that can embed this platform in an iFrame') . '</h3>',
            ['iframeSourceOption', 'iframeSourceDomains']
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
    private function setPostData()
    {
        if (isset($_POST['iframeSourceOption']) && array_key_exists($_POST['iframeSourceOption'], $this->getSourceOptions())) {
            $this->sourceElement->setValue($_POST['iframeSourceOption']);
        }

        if (isset($_POST['iframeSourceDomains'])) {
            $this->sourceDomainsElement->setValue($_POST['iframeSourceDomains']);
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



}
