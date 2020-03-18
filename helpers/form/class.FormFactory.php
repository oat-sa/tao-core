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

use oat\tao\helpers\form\WidgetRegistry;

/**
 * The FormFactory enable you to create ready-to-use instances of the Form
 * It helps you to get the commonly used instances for the default rendering
 * etc.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 */
class tao_helpers_form_FormFactory
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * The rendering mode of the form.
     *
     * @access protected
     * @var string
     */
    protected static $renderMode = 'xhtml';

    // --- OPERATIONS ---

    /**
     * Define the rendering mode
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string $renderMode
     */
    public static function setRenderMode($renderMode)
    {
        self::$renderMode = $renderMode;
    }

    /**
     * Factors an instance of Form
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string $name
     * @param  array $options
     * @return tao_helpers_form_Form
     * @throws common_Exception
     */
    public static function getForm($name = '', array $options = [])
    {
        $returnValue = null;

        //use the right implementation (depending the render mode)
        //@todo refactor this and use a FormElementFactory
        if (self::$renderMode === 'xhtml') {
            $myForm = new tao_helpers_form_xhtml_Form($name, $options);

            $myForm->setDecorators([
                'element' => new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div']),
                'group' => new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div', 'cssClass' => 'form-group']),
                'error' => new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div', 'cssClass' => 'form-error']),
                'actions-bottom' => new tao_helpers_form_xhtml_TagWrapper(['tag' => 'div', 'cssClass' => 'form-toolbar'])
            ]);

            $myForm->setActions(self::getCommonActions());
        } else {
            throw new common_Exception(sprintf('render mode {%s} not yet supported', self::$renderMode));
        }

        $returnValue = $myForm;

        return $returnValue;
    }

    /**
     * Create dynamically a Form Element instance of the defined type
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string $name
     * @param  string $widgetId
     * @return tao_helpers_form_FormElement
     * @throws common_Exception
     * @throws Exception
     */
    public static function getElement($name = '', $widgetId = '')
    {
        $eltClass = null;
        $definition = WidgetRegistry::getWidgetDefinitionById($widgetId);

        if ($definition === null || !isset($definition['renderers'][self::$renderMode])) {
            // could be a "pseudo" widget that has not been registered
            $candidates = [
                'tao_helpers_form_elements_xhtml_' . $widgetId,
                $widgetId
            ];

            foreach ($candidates as $candidate) {
                if (class_exists($candidate)) {
                    $eltClass = $candidate;
                    break;
                }
            }
        } else {
            $eltClass = $definition['renderers'][self::$renderMode];
        }

        if ($eltClass !== null) {
            $returnValue = new $eltClass($name);
            if (!$returnValue instanceof tao_helpers_form_FormElement) {
                throw new common_Exception(sprintf('%s must be a tao_helpers_form_FormElement', $eltClass));
            }
        } else {
            $returnValue = null;
            common_Logger::w(sprintf('Widget type with id %s not yet supported', $widgetId), ['FORM']);
        }

        return $returnValue;
    }

    /**
     * @param $name
     * @param core_kernel_classes_Resource $widget
     * @return mixed
     * @throws common_Exception
     * @throws common_exception_Error
     */
    public static function getElementByWidget($name, core_kernel_classes_Resource $widget)
    {
        $definition = WidgetRegistry::getWidgetDefinition($widget);
        if ($definition === null || !isset($definition['renderers'][self::$renderMode])) {
            throw new common_exception_Error(
                sprintf('Widget %s not supported in render mode %s', $widget->getUri(), self::$renderMode)
            );
        }

        $eltClass = $definition['renderers'][self::$renderMode];
        $returnValue = new $eltClass($name);

        if (!$returnValue instanceof tao_helpers_form_FormElement) {
            throw new common_Exception("$eltClass must be a tao_helpers_form_FormElement");
        }
        return $returnValue;
    }

    /**
     * Get an instance of a Validator
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string $name
     * @param  array $options
     * @return tao_helpers_form_Validator
     */
    public static function getValidator($name, $options = [])
    {
        $returnValue = null;

        $class = 'tao_helpers_form_validators_' . $name;
        if (class_exists($class)) {
            $returnValue = new $class($options);
        } else {
            common_Logger::w('Unknown validator ' . $name, ['TAO', 'FORM']);
        }

        return $returnValue;
    }

    /**
     * Get the common actions: save and revert
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string $context deprecated
     * @param  bool $save
     * @return array
     * @throws common_Exception
     */
    public static function getCommonActions($context = 'bottom', $save = true)
    {
        if (!$save)
        {
            return [tao_helpers_form_FormFactory::getElement('save', 'Free')];
        }

        $action = tao_helpers_form_FormFactory::getElement('Save', 'Button');
        $action->setIcon('icon-save');
        $action->setValue(__('Save'));
        $action->setType('submit');
        $action->addClass('form-submitter btn-success small');

        return [$action];
    }
}
