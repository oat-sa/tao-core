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
 *               2020-2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

use oat\oatbox\service\ServiceManager;
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\model\security\xsrf\TokenService;
use tao_helpers_form_FormFactory as FormFactory;
use oat\tao\helpers\form\elements\xhtml\CsrfToken;
use oat\tao\helpers\form\elements\FormElementAware;
use oat\tao\helpers\form\Feeder\SanitizerValidationFeeder;
use oat\tao\helpers\form\validators\CrossElementEvaluationAware;
use oat\tao\helpers\form\Feeder\SanitizerValidationFeederInterface;

/**
 * This class provide a container for a specific form instance.
 * Its subclasses instantiate a form.
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 */
abstract class tao_helpers_form_FormContainer
{
    public const CSRF_PROTECTION_OPTION = 'csrf_protection';
    public const IS_DISABLED = 'is_disabled';
    public const ADDITIONAL_VALIDATORS = 'extraValidators';
    public const ATTRIBUTE_VALIDATORS = 'attributeValidators';

    public const WITH_SERVICE_MANAGER = 'withServiceManager';

    /**
     * the form instance contained
     *
     * @var tao_helpers_form_Form
     */
    protected $form;

    /**
     * the data of the form
     *
     * @var array
     */
    protected $data = [];

    /**
     * the form options
     *
     * @var array
     */
    protected $options = [];

    /**
     * static list of all instantiated forms
     *
     * @var array
     */
    protected static $forms = [];

    /**
     * @var array
     */
    private $postData = [];

    /** @var SanitizerValidationFeederInterface */
    private $sanitizerValidationFeeder;

    /** @var ServiceManager */
    private $serviceManager;

    /**
     * The constructor, initialize and build the form
     * regarding the initForm and initElements methods
     * to be overridden
     *
     * @throws common_Exception
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     */
    public function __construct(array $data = [], array $options = [])
    {
        $this->withServiceManager($options);

        $this->data = $data;
        $this->options = $options;

        // initialize the form attribute
        $this->initForm();

        if ($this->form !== null) {
            // set the refs of all the forms there
            self::$forms[$this->form->getName()] = $this->form;

            $this
                ->getSanitizerValidationFeeder()
                ->setForm($this->form);
        }

        // initialize the elements of the form
        $this->initElements();

        if ($this->form !== null) {
            $this->form->evaluateInputValues();

            $this->applyAdditionalValidationRules($options);
            $this->applyAttributeValidators($options);

            if (($options[self::CSRF_PROTECTION_OPTION] ?? false) === true) {
                $this->initCsrfProtection();
            }

            // set the values in case of default values
            if (is_array($this->data) && !empty($this->data)) {
                $this->form->setValues($this->data);
            }

            if ($options[self::IS_DISABLED] ?? false) {
                $this->form->disable();
            }

            $this->getSanitizerValidationFeeder()->feed();

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
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     */
    public function getForm(): ?tao_helpers_form_Form
    {
        return $this->form;
    }

    public function addSanitizerValidator(tao_helpers_form_Validator $validator, array $elements): self
    {
        $this->getSanitizerValidationFeeder()->addValidator($validator);

        foreach ($elements as $element) {
            if ($element instanceof tao_helpers_form_FormElement) {
                $this->getSanitizerValidationFeeder()->addElement($element);

                continue;
            }

            if (is_string($element)) {
                $this->getSanitizerValidationFeeder()->addElementByUri($element);

                continue;
            }

            throw new InvalidArgumentException(
                sprintf(
                    'Element provided to sanitizer must be an instance of %s or a string',
                    tao_helpers_form_FormElement::class
                )
            );
        }

        return $this;
    }

    /**
     * Must be overridden and must instantiate the form instance and put it in
     * form attribute
     *
     * @return void
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     */
    abstract protected function initForm();

    /**
     * Used to create the form elements and bind them to the form instance
     *
     * @return void
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     */
    abstract protected function initElements();

    /**
     * Allow global form validation.
     * Override this function to do it.
     *
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

    private function applyAdditionalValidationRules(array $options): void
    {
        $validationRules = $options[self::ADDITIONAL_VALIDATORS] ?? [];

        if (empty($validationRules)) {
            return;
        }

        foreach ($this->getForm()->getElements() as $element) {
            $validators = $validationRules[$element->getName()] ?? [];
            $element->addValidators($validators);
            $this->configureFormValidators($validators, $this->getForm());
            $this->getForm()->addElement($element);
        }
    }

    private function applyAttributeValidators(array $options): void
    {
        $attributeValidators = $options[self::ATTRIBUTE_VALIDATORS] ?? [];

        if (empty($attributeValidators)) {
            return;
        }

        foreach ($this->getForm()->getElements() as $element) {
            $attributes = $element->getAttributes();
            $validators = array_intersect_key($attributeValidators, $attributes);

            if (empty($validators)) {
                continue;
            }

            /** @var ValidatorInterface[] $validators */
            $validators = array_merge(...array_values($validators));

            $this->propagateElement($validators, $element);
            $this->configureFormValidators($validators, $this->getForm());

            $element->addValidators($validators);
            $this->getForm()->addElement($element);
        }
    }

    /**
     * @param ValidatorInterface[]|FormElementAware[] $validators
     */
    private function propagateElement(iterable &$validators, tao_helpers_form_FormElement $element): void
    {
        foreach ($validators as &$validator) {
            if ($validator instanceof FormElementAware) {
                $validator = clone $validator;
                $validator->setElement($element);
            }
        }
    }

    /**
     * @param ValidatorInterface[]|CrossElementEvaluationAware[] $validators
     */
    private function configureFormValidators(iterable $validators, tao_helpers_form_Form $form): void
    {
        foreach ($validators as $validator) {
            if ($validator instanceof CrossElementEvaluationAware) {
                $validator->acknowledge($form);
            }
        }
    }

    private function getSanitizerValidationFeeder(): SanitizerValidationFeederInterface
    {
        if (!isset($this->sanitizerValidationFeeder)) {
            $serviceManager = $this->serviceManager ?? ServiceManager::getServiceManager();
            $this->sanitizerValidationFeeder = $serviceManager->getContainer()->get(SanitizerValidationFeeder::class);
        }

        return $this->sanitizerValidationFeeder;
    }

    private function withServiceManager(array &$options): void
    {
        if (
            isset($options[self::WITH_SERVICE_MANAGER])
            && $options[self::WITH_SERVICE_MANAGER] instanceof ServiceManager
        ) {
            $this->serviceManager = $options[self::WITH_SERVICE_MANAGER];
        }

        unset($options[self::WITH_SERVICE_MANAGER]);
    }
}
