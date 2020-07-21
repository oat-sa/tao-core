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

use oat\oatbox\validator\ValidatorInterface;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;

// Defining aliases for old style class names for backward compatibility
class_alias(SearchTextBox::class, \tao_helpers_form_elements_xhtml_Searchtextbox::class);

/**
 * Represents a form. It provides the default behavior for form management and
 * be overridden for any rendering mode.
 * A form is composed by a set of FormElements.
 *
 * The form data flow is:
 * 1. add the elements to the form instance
 * 2. run evaluate (initElements, update states (submited, valid, etc), update)
 * 3. render form
 *
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
abstract class tao_helpers_form_FormElement
{
    public const WIDGET_ID = '';

    /**
     * the name of the element
     *
     * @access protected
     * @var string
     */
    protected $name = '';

    /**
     * the value of the element
     *
     * @access protected
     * @var mixed
     */
    protected $value;

    /**
     * the list of element attributes (key/value pairs)
     *
     * @access protected
     * @var array
     */
    protected $attributes = [];

    /**
     * the widget links to the element
     *
     * @access protected
     * @deprecated
     * @see tao_helpers_form_FormElement::WIDGET_ID
     */
    protected $widget = '';

    /**
     * the element description
     *
     * @access protected
     * @var string
     */
    protected $description = '';

    /**
     * used to display an element regarding the others
     *
     * @access protected
     * @var int
     */
    protected $level = 1;

    /**
     * The list of validators links to the elements
     *
     * @access protected
     * @var array
     */
    protected $validators = [];

    /**
     * the error message to display when the element validation has failed
     *
     * @access protected
     * @var array
     */
    protected $error = [];

    /**
     * to force the validation of the element
     *
     * @access protected
     * @var bool
     */
    protected $forcedValid = false;

    /**
     * Stop or not after first validation error occurred
     * @var bool
     */
    protected $breakOnFirstError = true;

    /**
     * add a unit to the element (only for rendering purposes)
     *
     * @access protected
     * @var string
     */
    protected $unit = '';

    /**
     * Short description of attribute help
     *
     * @access protected
     * @var string
     */
    protected $help = '';

    /**
     * Short description of method __construct
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  string $name
     * @return void
     */
    public function __construct($name = '')
    {
        $this->name = $name;
    }

    /**
     * Short description of method getName
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @return string
     */
    public function getName()
    {
        return (string)$this->name;
    }

    /**
     * Short description of method setName
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the raw data of the request, that was stored by the feed function
     * the element. Mainly used by the Validators
     *
     * @author joel bout, joel@taotesting.com
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->value;
    }

    /**
     * Short description of method setValue
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Add a CSS class jQuery style
     *
     * @author Dieter Raber, <dieter@taotesting.com>
     * @param string $className
     */
    public function addClass($className)
    {
        $existingClasses = !empty($this->attributes['class'])
            ? explode(' ', $this->attributes['class'])
            : [];
        $existingClasses[] = $className;
        $this->attributes['class'] = implode(' ', array_unique($existingClasses));
    }


    /**
     * Remove a CSS class jQuery style
     *
     * @author Dieter Raber, <dieter@taotesting.com>
     * @param string $className
     */
    public function removeClass($className)
    {
        $existingClasses = !empty($this->attributes['class'])
            ? explode(' ', $this->attributes['class'])
            : [];
        unset($existingClasses[array_search($className, $existingClasses, true)]);
        $this->attributes['class'] = implode(' ', $existingClasses);
    }


    /**
     * Short description of method addAttribute
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  string $key
     * @param  string $value
     * @return void
     */
    public function addAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }


    /**
     * Short description of method setAttribute
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  string $key
     * @param  string $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Short description of method setAttributes
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  array $attributes
     * @return void
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Disables a UI input
     */
    public function disable()
    {
        $this->addAttribute('disabled', 'disabled');
    }

    /**
     * Short description of method renderAttributes
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @return string
     */
    protected function renderAttributes()
    {
        $returnValue = '';

        foreach ($this->attributes as $key => $value) {
            $returnValue .= " {$key}='{$value}' ";
        }

        return $returnValue;
    }

    /**
     * Short description of method getWidget
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @return string
     */
    public function getWidget(): string
    {
        return $this->widget ?: static::WIDGET_ID;
    }

    /**
     * Short description of method getDescription
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @return string
     */
    public function getDescription()
    {

        if (empty($this->description)) {
            $returnValue = ucfirst(strtolower($this->name));
        } else {
            $returnValue = $this->description;
        }

        return (string) $returnValue;
    }

    /**
     * Short description of method setDescription
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Short description of method setUnit
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  string $unit
     * @return void
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * Short description of method getLevel
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @return int
     */
    public function getLevel()
    {
        return (int) $this->level;
    }

    /**
     * Short description of method setLevel
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  int $level
     * @return void
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * Short description of method addValidator
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param ValidatorInterface $validator
     */
    public function addValidator(ValidatorInterface $validator)
    {
        if ($validator instanceof tao_helpers_form_validators_NotEmpty) {
            $this->addAttribute('required', true);
        }
        $this->validators[] = $validator;
    }

    /**
     * Short description of method addValidators
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  array $validators
     * @return void
     */
    public function addValidators($validators)
    {
        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }
    }

    /**
     * Short description of method setForcedValid
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     */
    public function setForcedValid()
    {
        $this->forcedValid = true;
    }

    /**
     * Short description of method validate
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @return bool
     */
    public function validate()
    {
        $returnValue = true;

        if (!$this->forcedValid) {
            foreach ($this->validators as $validator) {
                if (!$validator->evaluate($this->getRawValue())) {
                    $this->error[] = $validator->getMessage();
                    $returnValue = false;
                    common_Logger::d($this->getName() . ' is invalid for ' . $validator->getName(), ['TAO']);
                    if ($this->isBreakOnFirstError()) {
                        break;
                    }
                }
            }
        }

        return $returnValue;
    }

    /**
     * Short description of method getError
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @return string
     */
    public function getError()
    {
        return implode("\n", $this->error);
    }

    /**
     * Short description of method setHelp
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  string $help
     * @return void
     */
    public function setHelp($help)
    {
        $this->help = $help;
    }

    /**
     * Short description of method getHelp
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @return string
     */
    public function getHelp()
    {
        return (string) $this->help;
    }

    /**
     * Short description of method removeValidator
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @param  string $name
     * @return bool
     */
    public function removeValidator($name)
    {
        $returnValue = false;

        $name = (string) $name;
        if (strpos($name, 'tao_helpers_form_validators_') === 0) {
            $name = str_replace('tao_helpers_form_validators_', '', $name);
        }
        if (isset($this->validators[$name])) {
            unset($this->validators[$name]);
            $returnValue = true;
        }

        return $returnValue;
    }

    /**
     * Reads the submitted data into the form element
     *
     * @author Joel Bout, <joel@taotesting.com>
     */
    public function feed()
    {
        if (
            isset($_POST[$this->name])
            && $this->name !== 'uri' && $this->name !== 'classUri'
        ) {
            $this->setValue(tao_helpers_Uri::decode($_POST[$this->name]));
        }
    }

    /**
     * Returns the evaluated data that is calculated from the raw data and might
     * unchanged for simple form elements. Used for storage of the data.
     *
     * @author joel bout, joel@taotesting.com
     * @return string
     */
    public function getEvaluatedValue()
    {
        return tao_helpers_Uri::decode($this->getRawValue());
    }

    /**
     * Legacy code compliance method. When the getRawValue and the
     * methods were added, the fact that the getValue method was still invoked
     * TAO was not taken into account. To solve the problem, the getValue method
     * added to this class. Its implementation will call the getRawValue method
     * it has the same behaviour as the old getValue.
     *
     * @author Jérôme Bogaerts
     * @deprecated
     * @return mixed
     */
    public function getValue()
    {
        common_Logger::d('deprecated function getValue() called', [ 'TAO', 'DEPRECATED' ]);

        return $this->getRawValue();
    }

    /**
     * @return bool
     */
    public function isBreakOnFirstError()
    {
        return $this->breakOnFirstError;
    }

    /**
     * @param bool $breakOnFirstError
     */
    public function setBreakOnFirstError($breakOnFirstError)
    {
        $this->breakOnFirstError = $breakOnFirstError;
    }

    /**
     * Will render the Form Element.
     *
     */
    abstract public function render();
}
