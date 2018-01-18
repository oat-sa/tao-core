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

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;

/**
 * Short description of class tao_actions_form_Clazz
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 */
class tao_actions_form_Clazz
    extends tao_helpers_form_FormContainer
{
    /**
     * @var core_kernel_classes_Class
     */
    protected $clazz;

    /**
     * Mode to use for the authored properties (simple or advanced)
     *
     * @var string
     */
    protected $propertyMode;

    /**
     * Property values that are currently being threated
     *
     * @var array
     */
    protected $propertyData;

    /**
     * @param core_kernel_classes_Class $clazz
     * @param array $classData
     * @param array $propertyData
     * @param string $propertyMode
     */
    public function __construct( core_kernel_classes_Class $clazz, $classData, $propertyData, $propertyMode)
    {
        $this->clazz 	= $clazz;
        $this->propertyData = $propertyData;
        $this->propertyMode = $propertyMode;
        parent::__construct($classData);
    }

    /**
     * Class instance being authored
     *
     * @return core_kernel_classes_Class
     */
    protected function getClassInstance()
    {
        return $this->clazz;
    }

    /**
     * Top level class until which all properties
     * should be displayed
     *
     * @return core_kernel_classes_Class
     */
    protected function getTopClazz()
    {
        return new core_kernel_classes_Class(GenerisRdf::CLASS_GENERIS_RESOURCE);
    }

    /**
     * Returns the form for the property, based on the mode
     *
     * @param core_kernel_classes_Property $property
     * @param integer $index
     * @param boolean $isParentProp
     * @param array $propData
     */
    protected function getPropertyForm($property, $index, $isParentProp, $propData)
    {
        $propFormClass = 'tao_actions_form_' . ucfirst(strtolower($this->propertyMode)) . 'Property';
        if (!class_exists($propFormClass)) {
            $propFormClass = 'tao_actions_form_SimpleProperty';
        }
        $propFormContainer = new $propFormClass($this->getClassInstance(), $property, array('index' => $index, 'isParentProperty' => $isParentProp ), $propData);
        return $propFormContainer->getForm();
    }

    /**
     * Initialize the form
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initForm()
    {
        (isset($this->options['name'])) ? $name = $this->options['name'] : $name = '';
        if (empty($name)) {
            $name = 'form_' . (count(self::$forms) + 1);
        }
        unset($this->options['name']);

        $this->form = tao_helpers_form_FormFactory::getForm($name, $this->options);

        //add property action in toolbar
        $actions     = tao_helpers_form_FormFactory::getCommonActions();
        $propertyElt = tao_helpers_form_FormFactory::getElement('property', 'Free');
        $propertyElt->setValue(
            "<a href='#' class='btn-info property-adder small'><span class='icon-property-add'></span> " . __('Add property') . "</a>"
        );
        $actions[] = $propertyElt;

        //property mode
        $propModeELt = tao_helpers_form_FormFactory::getElement('propMode', 'Free');
        if($this->propertyMode == 'advanced'){
            $propModeELt->setValue("<a href='#' class='btn-info property-mode small property-mode-simple'><span class='icon-property-advanced'></span> ".__('Simple Mode')."</a>");
        }
        else{
            $propModeELt->setValue("<a href='#' class='btn-info property-mode small property-mode-advanced'><span class='icon-property-advanced'></span> ".__('Advanced Mode')."</a>");
        }

        $actions[] = $propModeELt;

        //add a hidden field that states it is a class edition form.
        $classElt = tao_helpers_form_FormFactory::getElement('tao.forms.class', 'Hidden');
        $classElt->setValue('1');
        $classElt->addClass('global');
        $this->form->addElement($classElt);

        $this->form->setActions($actions, 'top');
        $this->form->setActions($actions, 'bottom');


    }

    /**
     * Initialize the form elements
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initElements()
    {


        $clazz = $this->getClassInstance();

        //add a group form for the class edition
        $elementNames = array();
        foreach (tao_helpers_form_GenerisFormFactory::getDefaultProperties() as $property) {

            //map properties widgets to form elements
            $element = tao_helpers_form_GenerisFormFactory::elementMap($property);
            if (!is_null($element)) {

                //take property values to populate the form
                $values = $clazz->getPropertyValues($property);
                if (!$property->isMultiple()) {
                    if (count($values) > 1) {
                        $values = array_slice($values, 0, 1);
                    }
                }
                foreach ($values as $value) {
                    if (!is_null($value)) {
                        $element->setValue($value);
                    }
                }
                $element->setName('class_' . $element->getName());

                //set label validator
                if ($property->getUri() == OntologyRdfs::RDFS_LABEL) {
                    $element->addValidators(
                        array(
                            tao_helpers_form_FormFactory::getValidator('NotEmpty'),
                        )
                    );

                    $ns = substr($clazz->getUri(), 0, strpos($clazz->getUri(), '#'));
                    if ($ns != LOCAL_NAMESPACE) {
                        $readonly = tao_helpers_form_FormFactory::getElement($element->getName(), 'Readonly');
                        $readonly->setDescription($element->getDescription());
                        $readonly->setValue($element->getRawValue());
                        $element = $readonly;
                    }
                }
                $element->addClass('global');
                $this->form->addElement($element);

                $elementNames[] = $element->getName();
            }
        }

        //add an hidden elt for the class uri
        $classUriElt = tao_helpers_form_FormFactory::getElement('classUri', 'Hidden');
        $classUriElt->setValue(tao_helpers_Uri::encode($clazz->getUri()));
        $classUriElt->addClass('global');
        $this->form->addElement($classUriElt);
        
        $hiddenId = tao_helpers_form_FormFactory::getElement('id', 'Hidden');
        $hiddenId->setValue($clazz->getUri());
        $hiddenId->addClass('global');
        $this->form->addElement($hiddenId);
        

        $localNamespace = common_ext_NamespaceManager::singleton()->getLocalNamespace()->getUri();


        //class properties edition: add a group form for each property

        $classProperties = tao_helpers_form_GenerisFormFactory::getClassProperties($clazz, $this->getTopClazz());

        $i = 0;
        $systemProperties = $this->getSystemProperties();

        foreach ($classProperties as $classProperty) {
            $i++;
            $useEditor = (boolean)preg_match("/^" . preg_quote($localNamespace, '/') . "/", $classProperty->getUri());
            
            $parentProp = true;
            $domains    = $classProperty->getDomain();
            foreach ($domains->getIterator() as $domain) {

                if (array_search($classProperty->getUri(), $systemProperties) !== false || $domain->getUri() == $clazz->getUri() ) {
                    $parentProp = false;
                    //@todo use the getPrivileges method once implemented
                    break;
                }
            }

            if ($useEditor) {

                $propData = array();
                if (isset($this->propertyData[$classProperty->getUri()])) {
                    foreach ($this->propertyData[$classProperty->getUri()] as $key => $value) {
                        $propData[$i.'_'.$key] = $value;
                    }
                }

                $propForm = $this->getPropertyForm($classProperty, $i, $parentProp, $propData);

                //and get its elements and groups
                $this->form->setElements(array_merge($this->form->getElements(), $propForm->getElements()));
                $this->form->setGroups(array_merge($this->form->getGroups(), $propForm->getGroups()));

                unset($propForm);
            }
            // read only properties
            else {
                $roElement = tao_helpers_form_FormFactory::getElement('roProperty' . $i, 'Free');
                $roElement->setValue(__('Cannot be edited'));
                $this->form->addElement($roElement);

                $groupTitle = '<span class="property-heading-label">' . _dh($classProperty->getLabel()) . '</span>';
                $this->form->createGroup("ro_property_{$i}", $groupTitle, array('roProperty' . $i));
            }
        }
    }

    /**
     * Returns list of all system property classes
     * @return array
     */
    protected function getSystemProperties()
    {
        $constants = get_defined_constants(true);

        $keys = array_filter(array_keys($constants['user']), function ($key) {
            return strstr($key, 'PROPERTY') !== false;

        });

        return array_intersect_key($constants['user'], array_flip($keys));
    }
}