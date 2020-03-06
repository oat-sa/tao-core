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

/**
 * Represents a form. It provides the default behavior for form management and
 * be overridden for any rendering mode.
 * A form is composed by a set of FormElements.
 *
 * The form data flow is:
 * 1. add the elements to the form instance
 * 2. run evaluate (initElements, update states (submited, valid, etc), update
 * )
 * 3. render form
 *
 * @abstract
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao

 */
abstract class tao_helpers_form_Form
{

    /**
     * the form name
     *
     * @access protected
     * @var string
     */
    protected $name = '';

    /**
     * the list of element composing the form
     *
     * @access protected
     * @var tao_helpers_form_FormElement[]
     */
    protected $elements = [];

    /**
     * the actions of the form by context
     *
     * @access protected
     * @var tao_helpers_form_FormElement[][]
     */
    protected $actions = [];

    /**
     * if the form is valid or not
     *
     * @access public
     * @var bool
     */
    public $valid = false;

    /**
     * if the form has been submited or not
     *
     * @access protected
     * @var bool
     */
    protected $submited = false;

    /**
     * It represents the logicall groups
     *
     * @access protected
     * @var array
     */
    protected $groups = [];

    /**
     * The list of Decorator linked to the form
     *
     * @access protected
     * @var array
     */
    protected $decorators = [];

    /**
     * The form's options
     *
     * @access protected
     * @var array
     */
    protected $options = [];

    /**
     * Global form error message
     *
     * @access public
     * @var string
     */
    public $error = '';

    /**
     * List of fields names that are system only and which values doesn't need to be returned by `getValues()` call
     *
     * @access protected
     * @var array
     */
    protected $systemElements = [];

    /**
     * the form constructor
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $name
     * @param  array $options
     */
    public function __construct($name = '', array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * set the form name
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the form name
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return string
     */
    public function getName()
    {
        return (string) $this->name;
    }

    /**
     * set the form options
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Has an element of the form identified by it's name
     *
     * @param  string $name
     *
     * @return bool
     */
    public function hasElement($name)
    {
        foreach ($this->elements as $element) {
            if ($element->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * get an element of the form identified by it's name
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $name
     * @return tao_helpers_form_FormElement
     */
    public function getElement($name)
    {
        $returnValue = null;

        foreach ($this->elements as $element) {
            if ($element->getName() === $name) {
                $returnValue = $element;
                break;
            }
        }
        if ($returnValue === null) {
            common_Logger::w('Element with name "' . $name . '" not found');
        }


        return $returnValue;
    }

    /**
     * get all the form elements
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Define the list of form elements
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  array $elements
     */
    public function setElements(array $elements)
    {
        $this->elements = $elements;
    }

    /**
     * Remove an element identified by it's name.
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $name
     * @return bool
     */
    public function removeElement($name)
    {
        $returnValue = false;

        foreach ($this->elements as $index => $element) {
            if ($element->getName() === $name) {
                unset($this->elements[$index]);
                $groupName = $this->getElementGroup($name);
                if (!empty($groupName) && isset($this->groups[$groupName]['elements'][$name])) {
                    unset($this->groups[$groupName]['elements'][$name]);
                }
                $returnValue = true;
            }
        }

        return $returnValue;
    }

    /**
     * Add an element to the form
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  tao_helpers_form_FormElement $element
     * @param bool|false $isSystem
     */
    public function addElement(tao_helpers_form_FormElement $element, $isSystem = false)
    {
        $elementPosition = -1;
        foreach ($this->elements as $i => $elt) {
            if ($elt->getName() === $element->getName()) {
                $elementPosition = $i;
                break;
            }
        }

        if ($elementPosition >= 0) {
            $this->elements[$elementPosition] = $element;
        } else {
            $this->elements[] = $element;
        }

        if ($isSystem) {
            $this->systemElements[] = $element->getName();
        }
    }

    /**
     * Define the form actions for a context.
     * The different contexts are top and bottom.
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     *
     * @param  array $actions
     * @param  string $context
     *
     * @throws Exception
     */
    public function setActions($actions, $context = 'bottom')
    {
        $this->actions[$context] = [];

        foreach ($actions as $action) {
            if (!$action instanceof tao_helpers_form_FormElement) {
                throw new Exception('The actions parameter must only contains instances of tao_helpers_form_FormElement');
            }
            $this->actions[$context][] = $action;
        }
    }

    /**
     * Get the defined actions for a context
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $context
     * @return array
     */
    public function getActions($context = 'bottom')
    {
        $returnValue = [];

        if (isset($this->actions[$context])) {
            $returnValue = $this->actions[$context];
        }

        return (array) $returnValue;
    }

    /**
     * Get specific action element from context
     * @param $name
     * @param string $context
     *
     * @return mixed
     */
    public function getAction($name, $context = 'bottom')
    {
        $returnValue = null;

        foreach ($this->getActions($context) as $element) {
            if ($element->getName() === $name) {
                $returnValue = $element;
                break;
            }
        }
        if ($returnValue === null) {
            common_Logger::w('Action with name \'' . $name . '\' not found');
        }

        return $returnValue;
    }

    /**
     * Set the decorator of the type defined in parameter.
     * The different types are element, error, group.
     * By default it uses the element decorator.
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     *
     * @param tao_helpers_form_Decorator $decorator
     * @param string $type type
     */
    public function setDecorator(tao_helpers_form_Decorator $decorator, $type = 'element')
    {
        $this->decorators[$type] = $decorator;
    }

    /**
     * Set the form decorators
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  array decorators
     */
    public function setDecorators($decorators)
    {
        foreach ($decorators as $type => $decorator) {
            $this->setDecorator($decorator, $type);
        }
    }

    /**
     * Get the decorator of the type defined in parameter.
     * The different types are element, error, group.
     * By default it uses the element decorator.
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $type
     * @return tao_helpers_form_Decorator
     */
    public function getDecorator($type = 'element')
    {
        $returnValue = null;


        if (array_key_exists($type, $this->decorators)) {
            $returnValue  = $this->decorators[$type];
        }


        return $returnValue;
    }

    /**
     * render all the form elements
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return string
     */
    public function renderElements()
    {
        $returnValue = '';


        foreach ($this->elements as $element) {
            if ($this->getElementGroup($element->getName()) !== '') {
                continue;
            }

            if ($this->getDecorator() !== null && !($element instanceof tao_helpers_form_elements_Hidden)) {
                $returnValue .= $this->getDecorator()->preRender();
            }

            if (!$this->isValid() && $element->getError()) {
                $element->addClass('error');
            }

            //render element
            $returnValue .= $element->render();

            //render element help
            $help = trim($element->getHelp());
            if (!empty($help)) {
                if ($this->getDecorator('help') !== null) {
                    $returnValue .= $this->getDecorator('help')->preRender();
                }

                $returnValue .= $help;

                if ($this->getDecorator('help') !== null) {
                    $returnValue .= $this->getDecorator('help')->postRender();
                }
            }

            //render error message
            if (!$this->isValid() && $element->getError()) {
                if ($this->getDecorator('error') !== null) {
                    $returnValue .= $this->getDecorator('error')->preRender();
                }

                $returnValue .= nl2br($element->getError());

                if ($this->getDecorator('error') !== null) {
                    $returnValue .= $this->getDecorator('error')->postRender();
                }
            }

            if (!$element instanceof tao_helpers_form_elements_Hidden && $this->getDecorator() !== null) {
                $returnValue .= $this->getDecorator()->postRender();
            }
        }

        $subGroupDecorator = null;
        if ($this->getDecorator('group') instanceof tao_helpers_form_Decorator) {
            $decoratorClass = get_class($this->getDecorator('group'));
            $subGroupDecorator = new $decoratorClass();
        }

        //render group
        foreach ($this->groups as $groupName => $group) {
            if ($this->getDecorator('group') !== null) {
                $this->getDecorator('group')->setOption('id', $groupName);
                if (isset($group['options']['class'])) {
                    $cssClasses = explode(' ', $this->getDecorator('group')->getOption('cssClass'));
                    $currentClasses = array_map('trim', $cssClasses);
                    if (!in_array($group['options']['class'], $currentClasses, true)) {
                        $currentClasses[] = $group['options']['class'];
                        $this->getDecorator('group')->setOption(
                            'cssClass',
                            implode(' ', array_unique($currentClasses))
                        );
                    }
                }
                $returnValue .= $this->getDecorator('group')->preRender();
            }
            $returnValue .= $group['title'];
            if ($subGroupDecorator instanceof tao_helpers_form_Decorator) {
                $returnValue .= $subGroupDecorator->preRender();
            }
            foreach ($group['elements'] as $elementName) {
                if ($this->getElementGroup($elementName) === $groupName && $element = $this->getElement($elementName)) {
                    if ($this->getDecorator() !== null) {
                        $returnValue .= $this->getDecorator()->preRender();
                    }

                    //render element
                    if (! $this->isValid() && $element->getError()) {
                        $element->addClass('error');
                    }
                    $returnValue .= $element->render();

                    //render element help
                    $help = trim($element->getHelp());
                    if (!empty($help)) {
                        if ($this->getDecorator('help') !== null) {
                            $returnValue .= $this->getDecorator('help')->preRender();
                        }

                        $returnValue .= $help;

                        if ($this->getDecorator('help') !== null) {
                            $returnValue .= $this->getDecorator('help')->postRender();
                        }
                    }

                    //render error message
                    if (!$this->isValid() && $element->getError()) {
                        if ($this->getDecorator('error') !== null) {
                            $returnValue .= $this->getDecorator('error')->preRender();
                        }
                        $returnValue .= nl2br($element->getError());
                        if ($this->getDecorator('error') !== null) {
                            $returnValue .= $this->getDecorator('error')->postRender();
                        }
                    }

                    if ($this->getDecorator() !== null) {
                        $returnValue .= $this->getDecorator()->postRender();
                    }
                }
            }
            if ($subGroupDecorator instanceof tao_helpers_form_Decorator) {
                $returnValue .= $subGroupDecorator->postRender();
            }
            if ($this->getDecorator('group') !== null) {
                $returnValue .= $this->getDecorator('group')->postRender();
                $this->getDecorator('group')->setOption('id', '');
            }
        }


        return $returnValue;
    }

    /**
     * render the form actions by context
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $context
     * @return string
     */
    public function renderActions($context = 'bottom')
    {
        $returnValue = '';

        if (isset($this->actions[$context])) {
            $decorator = null;
            if ($this->getDecorator('actions-' . $context) !== null) {
                $decorator = $this->getDecorator('actions-' . $context);
            } elseif ($this->getDecorator('actions') !== null) {
                $decorator = $this->getDecorator('actions');
            }

            if ($decorator !== null) {
                $returnValue .= $decorator->preRender();
            }

            foreach ($this->actions[$context] as $action) {
                $returnValue .= $action->render();
            }

            if ($decorator !== null) {
                $returnValue .= $decorator->postRender();
            }
        }

        return $returnValue;
    }

    /**
     * Initialize the elements set
     */
    protected function initElements()
    {
    }

    /**
     * Check if the form contains a file upload element
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return bool
     */
    public function hasFileUpload()
    {
        $returnValue = false;

        foreach ($this->elements as $element) {
            if ($element instanceof tao_helpers_form_elements_File) {
                $returnValue = true;
                break;
            }
        }

        return $returnValue;
    }

    /**
     * Enables you to know if the form is valid
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Enables you to know if the form has been submited
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return bool
     */
    public function isSubmited()
    {
        return  $this->submited;
    }

    /**
     * Update manually the values of the form
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  array $values
     */
    public function setValues($values)
    {
        foreach ($values as $key => $value) {
            foreach ($this->elements as $element) {
                if ($element->getName() === $key) {
                    if (
                        $element instanceof tao_helpers_form_elements_Checkbox ||
                        (method_exists($element, 'setValues') && is_array($value))
                    ) {
                        $element->setValues($value);
                    } else {
                        $element->setValue($value);
                    }
                    break;
                }
            }
        }
    }

    /**
     * Disables the whole form
     */
    public function disable()
    {
        foreach ($this->elements as $element) {
            $element->disable();
        }

        foreach ($this->actions as $context) {
            foreach ($context as $action) {
                $action->disable();
            }
        }
    }

    /**
     * Get the current values of the form
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $groupName
     * @return array
     */
    abstract public function getValues($groupName = '');

    /**
     * get the current value of the element identified by the name in parameter
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $name
     * @return boolean
     */
    public function getValue($name)
    {
        foreach ($this->elements as $element) {
            if ($element->getName() === $name) {
                return  $element->getEvaluatedValue();
            }
        }

        return false;
    }

    /**
     * Short description of method getGroups
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Short description of method setGroups
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  array $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * Create a logical group of elements
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $groupName
     * @param  string $groupTitle
     * @param  array $elements array of form elements or their identifiers
     * @param  array $options
     *
     * @throws common_Exception
     */
    public function createGroup($groupName, $groupTitle = '', array $elements = [], array $options = [])
    {
        $identifier = [];
        foreach ($elements as $element) {
            if ($element instanceof tao_helpers_form_FormElement) {
                $identifier[] = $element->getName();
            } elseif (is_string($element)) {
                $identifier[] = $element;
            } else {
                throw new common_Exception('Unknown element of type ' . gettype($element) . ' in ' . __FUNCTION__);
            }
        }
        $this->groups[$groupName] = [
            'title'    => empty($groupTitle) ? $groupName : $groupTitle,
            'elements' => $identifier,
            'options'  => $options
        ];
    }

    /**
     * add an element to a group
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $groupName
     * @param  string $elementName
     */
    public function addToGroup($groupName, $elementName = '')
    {
        if (
            isset($this->groups[$groupName]['elements'])
            && !in_array(
                $elementName,
                $this->groups[$groupName]['elements'],
                true
            )
        ) {
            $this->groups[$groupName]['elements'][] = $elementName;
        }
    }

    /**
     * get the group where is an element
     *
     * @access protected
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $elementName
     * @return string
     */
    protected function getElementGroup($elementName)
    {
        $returnValue =  '';

        foreach ($this->groups as $groupName => $group) {
            if (in_array($elementName, $group['elements'], true)) {
                $returnValue = $groupName;
                break;
            }
        }

        return $returnValue;
    }

    /**
     * remove the group identified by the name in parameter
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string $groupName
     */
    public function removeGroup($groupName)
    {
        if (isset($this->groups[$groupName])) {
            foreach ($this->groups[$groupName]['elements'] as $element) {
                $this->removeElement($element);
            }
            unset($this->groups[$groupName]);
        }
    }

    /**
     * evaluate the form inside the current context. Must be overridden, for
     * rendering mode: for example, it's used to populate and validate the data
     * the http request for an xhtml context
     *
     * @abstract
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return mixed
     */
    abstract public function evaluate();

    /**
     * Render the form. Must be overridden for each rendering mode.
     *
     * @abstract
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return string
     */
    abstract public function render();
}
