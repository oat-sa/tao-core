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
 *               2009-2020 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

class tao_actions_form_RemoteList extends tao_helpers_form_FormContainer
{
    public const FIELD_NAME = 'name';
    public const FIELD_SOURCE_URL = 'source';
    public const FIELD_ITEM_LABEL_PATH = 'label_path';
    public const FIELD_ITEM_URI_PATH = 'uri_path';
    public const FIELD_DEPENDENCY_ITEM_URI_PATH = 'dependency_uri_path';

    public const IS_LISTS_DEPENDENCY_ENABLED = 'isListsDependencyEnabled';

    /**
     * Short description of method initForm
     *
     * @access public
     * @return mixed
     * @throws common_Exception
     */
    public function initForm()
    {
        $this->form = tao_helpers_form_FormFactory::getForm('list');

        $addElt = tao_helpers_form_FormFactory::getElement('add', 'Free');

        $addElt->setValue('<a href="#" class="form-submitter btn-success small"><span class="icon-add"></span> ' . __('Add') . '</a>');
        $this->form->setActions([$addElt], 'bottom');
        $this->form->setActions([], 'top');
    }

    /**
     * @access public
     * @throws common_Exception
     */
    public function initElements()
    {
        $this->createTextBoxElement(self::FIELD_NAME, __('Name'));
        $this->createTextBoxElement(self::FIELD_SOURCE_URL, __('Data source URI'));
        $this->createTextBoxElement(self::FIELD_ITEM_LABEL_PATH, __('Label Path'));
        $this->createTextBoxElement(self::FIELD_ITEM_URI_PATH, __('URI Path'));

        if (($this->options[self::IS_LISTS_DEPENDENCY_ENABLED] ?? false)) {
            $this->createTextBoxElement(
                self::FIELD_DEPENDENCY_ITEM_URI_PATH,
                __('Dependency URI Path'),
                false
            );
        }
    }

    /**
     * @throws common_Exception
     */
    private function createTextBoxElement(string $name, string $label, bool $addNotEmptyValidator = true): void
    {
        $element = tao_helpers_form_FormFactory::getElement($name, 'Textbox');
        $element->setDescription($label);

        if ($addNotEmptyValidator) {
            $element->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
        }

        $this->form->addElement($element);
    }
}
