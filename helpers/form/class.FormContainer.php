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

use oat\tao\helpers\form\elements\xhtml\CsrfToken;
use oat\tao\model\security\xsrf\TokenService;
use tao_helpers_form_FormFactory as FormFactory;

/**
 * This class provide a container for a specific form instance.
 * Its subclasses instantiate a form.
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 */
abstract class tao_helpers_form_FormContainer
{
    public const CSRF_PROTECTION_OPTION = 'csrf_protection';
    public const IS_DISABLED            = 'is_disabled';

    /**
     * the form instance contained
     *
     * @access protected
     * @var tao_helpers_form_Form
     */
    protected $form;

    /**
     * the data of the form
     *
     * @access protected
     * @var array
     */
    protected $data = [];

    /**
     * the form options
     *
     * @access protected
     * @var array
     */
    protected $options = [];

    /**
     * static list of all instantiated forms
     *
     * @access protected
     * @var array
     */
    protected static $forms = [];

    /**
     * @var array
     */
    private $postData = [];

    /**
     * The constructor, initialize and build the form
     * regarding the initForm and initElements methods
     * to be overridden
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  array data
     * @param  array options
     *
     * @throws common_Exception
     */
    public function __construct($data = [], $options = [])
    {
        $this->data = $data;
        $this->options = $options;

        // initialize the form attribute
        $this->initForm();

        if ($this->form !== null) {
            // set the refs of all the forms there
            self::$forms[$this->form->getName()] = $this->form;
        }

        // initialize the elements of the form
        $this->initElements();

        if (($options[self::CSRF_PROTECTION_OPTION] ?? false) === true) {
            $this->initCsrfProtection();
        }

        // set the values in case of default values
        if (is_array($this->data) && !empty($this->data)) {
            $this->form->setValues($this->data);
        }

        if ($this->form !== null) {
            if ($options[self::IS_DISABLED] ?? false)
            {
                $this->form->disable();
            }

            // evaluate the form
            $this->form->evaluate();
            //validate global form rules
            $this->validate();
        }

        if (!empty($_POST)) {
            $this->postData = $_POST;
        }
    }

    /**
     * Destructor (remove the current form in the static list)
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     */
    public function __destruct()
    {
        if ($this->form !== null) {
            unset(self::$forms[$this->form->getName()]);
        }
    }

    /**
     * get the form instance
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return tao_helpers_form_Form
     */
    public function getForm(): ?tao_helpers_form_Form
    {
        return $this->form;
    }

    /**
     * Must be overridden and must instantiate the form instance and put it in
     * form attribute
     *
     * @abstract
     * @access protected
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return mixed
     */
    abstract protected function initForm();

    /**
     * Used to create the form elements and bind them to the form instance
     *
     * @abstract
     * @access protected
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return mixed
     */
    abstract protected function initElements();

    /**
     * Allow global form validation.
     * Override this function to do it.
     *
     * @access protected
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     */
    protected function validate(): bool
    {
        return true;
    }

    /**
     * Return the posted form data.
     */
    protected function getPostData(): array
    {
        return $this->postData;
    }

    /**
     * Initialize the CSRF protection element for the form.
     * @throws common_Exception
     */
    private function initCsrfProtection(): void
    {
        $csrfTokenElement = FormFactory::getElement(TokenService::CSRF_TOKEN_HEADER, CsrfToken::class);
        $this->form->addElement($csrfTokenElement, true);
    }
}
