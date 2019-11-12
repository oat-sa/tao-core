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
 * It's subclasses instanciate a form and it's elements to be used as a
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 */
abstract class tao_helpers_form_FormContainer
{
    const CSRF_PROTECTION_OPTION = 'csrf_protection';

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
     * static list of all instanciated forms
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

        if (isset($options[self::CSRF_PROTECTION_OPTION]) && $options[self::CSRF_PROTECTION_OPTION] === true) {
            $this->initCsrfProtection();
        }

        // set the values in case of default values
        if ($this->data && count($this->data) > 0) {
            $this->form->setValues($this->data);
        }

        // evaluate the form
        if ($this->form !== null) {
            $this->form->evaluate();
        }

        //validate global form rules
        if ($this->form !== null) {
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
    public function getForm()
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
     * @return boolean
     */
    protected function validate()
    {
        return true;
    }

    /**
     * Return the posted form data.
     *
     * @return array
     */
    protected function getPostData()
    {
        return $this->postData;
    }

    /**
     * Initialize the CSRF protection element for the form.
     * @throws common_Exception
     */
    private function initCsrfProtection()
    {
        $csrfTokenElement = FormFactory::getElement(TokenService::CSRF_TOKEN_HEADER, CsrfToken::class);
        $this->form->addElement($csrfTokenElement, true);
    }
}
