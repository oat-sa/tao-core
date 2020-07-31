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
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\generis\model\WidgetRdf;
use oat\tao\helpers\form\elements\TreeAware;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\helpers\form\ValidationRuleRegistry;
use oat\tao\model\TaoOntology;
use tao_helpers_form_elements_AsyncFile as AsyncFile;
use tao_helpers_form_elements_Authoring as Authoring;
use tao_helpers_form_elements_Calendar as Calendar;
use tao_helpers_form_elements_Checkbox as Checkbox;
use tao_helpers_form_elements_Combobox as ComboBox;
use tao_helpers_form_elements_GenerisAsyncFile as GenerisAsyncFile;
use tao_helpers_form_elements_Hiddenbox as HiddenBox;
use tao_helpers_form_elements_Htmlarea as HtmlArea;
use tao_helpers_form_elements_Radiobox as RadioBox;
use tao_helpers_form_elements_Textarea as TextArea;
use tao_helpers_form_elements_Textbox as TextBox;
use tao_helpers_form_elements_Treebox as TreeBox;

/**
 * The GenerisFormFactory enables you to create Forms using rdf data and the
 * api to provide it. You can give any node of your ontology and the factory
 * create the appriopriate form. The Generis ontology (with the Widget Property)
 * required to use it.
 * Now only the xhtml rendering mode is implemented
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @see core_kernel_classes_* packages

 */
class tao_helpers_form_GenerisFormFactory
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Enable you to map an rdf property to a form element using the Widget
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     *
     * @param core_kernel_classes_Property $property
     *
     * @return tao_helpers_form_FormElement
     *
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws core_kernel_persistence_Exception
     */
    public static function elementMap(core_kernel_classes_Property $property): ?tao_helpers_form_FormElement
    {
        //create the element from the right widget
        $property->feed();

        $widgetResource = $property->getWidget();

        //authoring widget is not used in standalone mode
        if (
            null === $widgetResource
            || $widgetResource->getUri() === Authoring::WIDGET_ID
            && tao_helpers_Context::check('STANDALONE_MODE')
        ) {
            return null;
        }

        // horrible hack to fix file widget
        if ($widgetResource->getUri() === AsyncFile::WIDGET_ID) {
            $widgetResource = new core_kernel_classes_Resource(GenerisAsyncFile::WIDGET_ID);
        }

        $element = tao_helpers_form_FormFactory::getElementByWidget(tao_helpers_Uri::encode($property->getUri()), $widgetResource);

        if (null === $element) {
            return null;
        }

        if ($element->getWidget() !== $widgetResource->getUri()) {
            common_Logger::w('Widget definition differs from implementation: ' . $element->getWidget() . ' != ' . $widgetResource->getUri());
            return null;
        }

        //use the property label as element description
        $propDesc = trim($property->getLabel()) !== ''
            ? $property->getLabel()
            : str_replace(LOCAL_NAMESPACE, '', $property->getUri());
        $element->setDescription($propDesc);

        //multi elements use the property range as options
        if (method_exists($element, 'setOptions')) {
            $range = $property->getRange();

            if ($range !== null) {
                $options = [];

                if ($element instanceof TreeAware) {
                    $sortedOptions = $element->rangeToTree(
                        $property->getUri() === OntologyRdfs::RDFS_RANGE ? new core_kernel_classes_Class(OntologyRdfs::RDFS_RESOURCE) : $range
                    );
                } else {
                    foreach ($range->getInstances(true) as $rangeInstance) {
                        $level = $rangeInstance->getOnePropertyValue(new core_kernel_classes_Property(TaoOntology::PROPERTY_LIST_LEVEL));
                        if (is_null($level)) {
                            $options[tao_helpers_Uri::encode($rangeInstance->getUri())] = [tao_helpers_Uri::encode($rangeInstance->getUri()), $rangeInstance->getLabel()];
                        } else {
                            $level = ($level instanceof core_kernel_classes_Resource) ? $level->getUri() : (string)$level;
                            $options[$level] = [tao_helpers_Uri::encode($rangeInstance->getUri()), $rangeInstance->getLabel()];
                        }
                    }
                    ksort($options);
                    $sortedOptions = [];
                    foreach ($options as $id => $values) {
                        $sortedOptions[$values[0]] = $values[1];
                    }
                    //set the default value to an empty space
                    if (method_exists($element, 'setEmptyOption')) {
                        $element->setEmptyOption(' ');
                    }
                }

                //complete the options listing
                $element->setOptions($sortedOptions);
            }
        }

        foreach (ValidationRuleRegistry::getRegistry()->getValidators($property) as $validator) {
            $element->addValidator($validator);
        }

        return $element;
    }

    /**
     * Enable you to get the properties of a class.
     * The advantage of this method is to limit the level of recusrivity in the
     * It get the properties up to the defined top class
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  core_kernel_classes_Class $clazz
     * @param  core_kernel_classes_Class $topLevelClazz
     * @return array
     */
    public static function getClassProperties(
        core_kernel_classes_Class $clazz,
        core_kernel_classes_Class $topLevelClazz = null
    ): array {
        if (null === $topLevelClazz) {
            $topLevelClazz = new core_kernel_classes_Class(TaoOntology::CLASS_URI_OBJECT);
        }

        if ($clazz->getUri() === $topLevelClazz->getUri()) {
            return $clazz->getProperties(false);
        }

        //determine the parent path
        $parents = [];
        $top = false;
        do {
            if (!isset($lastLevelParents)) {
                $parentClasses = $clazz->getParentClasses(false);
            } else {
                $parentClasses = [];
                foreach ($lastLevelParents as $parent) {
                    $parentClasses = array_merge($parentClasses, $parent->getParentClasses(false));
                }
            }
            if (empty($parentClasses)) {
                break;
            }
            $lastLevelParents = [];
            foreach ($parentClasses as $parentClass) {
                if ($parentClass->getUri() === OntologyRdfs::RDFS_CLASS) {
                    continue;
                }
                if ($parentClass->getUri() === $topLevelClazz->getUri()) {
                    $parents[$parentClass->getUri()] = $parentClass;
                    $top = true;
                    break;
                }


                $allParentClasses = $parentClass->getParentClasses(true);
                if (array_key_exists($topLevelClazz->getUri(), $allParentClasses)) {
                     $parents[$parentClass->getUri()] = $parentClass;
                }

                $lastLevelParents[$parentClass->getUri()] = $parentClass;
            }
        } while (!$top);

        $propertyChunks = [[]];
        foreach ($parents as $parent) {
            $propertyChunks[] = $parent->getProperties(false);
        }
        $propertyChunks[] = $clazz->getProperties(false);

        return array_merge(...$propertyChunks);
    }

    /**
     * get the default properties to add to every forms
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return array
     */
    public static function getDefaultProperties(): array
    {
        return [
            new core_kernel_classes_Property(OntologyRdfs::RDFS_LABEL)
        ];
    }

    /**
     * Get the properties of the rdfs Property class
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string mode
     * @return array
     */
    public static function getPropertyProperties($mode = 'simple'): array
    {
        $returnValue = [];

        switch ($mode) {
            case 'simple':
                $defaultUris = [GenerisRdf::PROPERTY_IS_LG_DEPENDENT];
                break;
            case 'advanced':
            default:
                $defaultUris = [
                    OntologyRdfs::RDFS_LABEL,
                    WidgetRdf::PROPERTY_WIDGET,
                    OntologyRdfs::RDFS_RANGE,
                    GenerisRdf::PROPERTY_IS_LG_DEPENDENT
                ];
                break;
        }
        $resourceClass = new core_kernel_classes_Class(OntologyRdf::RDF_PROPERTY);
        foreach ($resourceClass->getProperties() as $property) {
            if (in_array($property->getUri(), $defaultUris, true)) {
                $returnValue[] = $property;
            }
        }

        return $returnValue;
    }

    /**
     * Return the map between the Property properties: range, widget, etc. to
     * shortcuts for the simplePropertyEditor
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return array
     */
    public static function getPropertyMap(): array
    {
        return [
            'text' => [
                'title'     => __('Text - Short - Field'),
                'widget'    => TextBox::WIDGET_ID,
                'range'     => OntologyRdfs::RDFS_LITERAL,
                'multiple'  => GenerisRdf::GENERIS_FALSE
            ],
            'longtext' => [
                'title'     => __('Text - Long - Box'),
                'widget'    => TextArea::WIDGET_ID,
                'range'     => OntologyRdfs::RDFS_LITERAL,
                'multiple'  => GenerisRdf::GENERIS_FALSE
            ],
            'html' => [
                'title'     => __('Text - Long - HTML editor'),
                'widget'    => HtmlArea::WIDGET_ID,
                'range'     => OntologyRdfs::RDFS_LITERAL,
                'multiple'  => GenerisRdf::GENERIS_FALSE
            ],
            'list' => [
                'title'     => __('List - Single choice - Radio button'),
                'widget'    => RadioBox::WIDGET_ID,
                'range'     => OntologyRdfs::RDFS_RESOURCE,
                'multiple'  => GenerisRdf::GENERIS_FALSE
            ],
            'multiplenodetree' => [
                'title'     => __('Tree - Multiple node choice '),
                'widget'    => TreeBox::WIDGET_ID,
                'range'     => OntologyRdfs::RDFS_RESOURCE,
                'multiple'  => GenerisRdf::GENERIS_TRUE
            ],

            'longlist' => [
                'title'     => __('List - Single choice - Drop down'),
                'widget'    => ComboBox::WIDGET_ID,
                'range'     => OntologyRdfs::RDFS_RESOURCE,
                'multiple'  => GenerisRdf::GENERIS_FALSE
            ],
            'multilist' => [
                'title'     => __('List - Multiple choice - Check box'),
                'widget'    => Checkbox::WIDGET_ID,
                'range'     => OntologyRdfs::RDFS_RESOURCE,
                'multiple'  => GenerisRdf::GENERIS_TRUE
            ],
            'multisearchlist' => [
                'title'     => __('List - Multiple choice - Search input'),
                'widget'    => SearchTextBox::WIDGET_ID,
                'range'     => OntologyRdfs::RDFS_RESOURCE,
                'multiple'  => GenerisRdf::GENERIS_TRUE
            ],
            'singlesearchlist' => [
                'title'     => __('List - Single choice - Search input'),
                'widget'    => SearchDropdown::WIDGET_ID,
                'range'     => OntologyRdfs::RDFS_RESOURCE,
                'multiple'  => GenerisRdf::GENERIS_FALSE
            ],
            'calendar' => [
                'title'     => __('Calendar'),
                'widget'    => Calendar::WIDGET_ID,
                'range'     => OntologyRdfs::RDFS_LITERAL,
                'multiple'  => GenerisRdf::GENERIS_FALSE
            ],
            'password' => [
                'title'     => __('Password'),
                'widget'    => HiddenBox::WIDGET_ID,
                'range'     => OntologyRdfs::RDFS_LITERAL,
                'multiple'  => GenerisRdf::GENERIS_FALSE
            ],
            'file' => [
                'title'     => __('File'),
                'widget'    => AsyncFile::WIDGET_ID,
                'range'     => GenerisRdf::CLASS_GENERIS_FILE,
                'multiple'  => GenerisRdf::GENERIS_FALSE
            ]
        ];
    }


    /**
     * Short description of method extractTreeData
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array $data
     * @return array
     */
    public static function extractTreeData($data): array
    {
        $returnValue = [];

        if (isset($data['data'])) {
            $data = [$data];
        }
        foreach ($data as $node) {
            $returnValue[$node['attributes']['id']] = $node['data'];
            if (isset($node['children'])) {
                $returnValue = array_merge($returnValue, self::extractTreeData($node['children']));
            }
        }

        return $returnValue;
    }
}
