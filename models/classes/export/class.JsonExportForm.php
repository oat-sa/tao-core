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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */
use oat\tao\helpers\Template;

/**
 * Export form
 *
 * @access public
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package tao
 */
class tao_models_classes_export_JsonExportForm extends tao_helpers_form_FormContainer
{
    /**
     * Initialize the export form
     *
     * @access public
     * @return mixed
     */
    public function initForm()
    {

        $this->form = new tao_helpers_form_xhtml_Form('export');

        $this->form->setDecorators(array(
            'element' => new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')),
            'group' => new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-group')),
            'error' => new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-error ui-state-error ui-corner-all')),
            'actions-bottom' => new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-toolbar')),
        ));

        $exportElt = tao_helpers_form_FormFactory::getElement('export', 'Free');
        $exportElt->setValue('<a href="#" class="form-submitter btn-success small"><span class="icon-export"></span> ' . __('Export') . '</a>');

        $this->form->setActions(array($exportElt), 'bottom');
    }

    /**
     * overriden
     *
     * @access public
     * @return mixed
     */
    public function initElements()
    {
        if (isset($this->data['instance'])) {
            $resource = $this->data['instance'];
        } elseif (isset($this->data['class'])) {
            $resource = $this->data['class'];
        } else {
            throw new common_Exception('No class nor instance specified for export');
        }

        $fileName = strtolower(tao_helpers_Display::textCleaner($resource->getLabel(), '*'));

        $hiddenElt = tao_helpers_form_FormFactory::getElement('resource', 'Hidden');
        $hiddenElt->setValue($resource->getUri());
        $this->form->addElement($hiddenElt);

        $nameElt = tao_helpers_form_FormFactory::getElement('filename', 'Textbox');
        $nameElt->setDescription(__('File name'));
        $nameElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
        $nameElt->setValue($fileName);
        $nameElt->setUnit(".json");
        $this->form->addElement($nameElt);

        $this->form->createGroup('options', __('Export data as JSON file'), array('filename', 'rdftpl'));
    }
}
