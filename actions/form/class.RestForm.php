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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

use \oat\generis\model\OntologyAwareTrait;
use \oat\tao\helpers\form\ValidationRuleRegistry;
use \oat\oatbox\validator\ValidatorInterface;
use oat\tao\model\TaoOntology;

/**
 * Class tao_actions_form_RestForm
 *
 * Form object to handle rdf resource
 * It manages data to create and edit a resource into a given class
 */
class tao_actions_form_RestForm
{
    use OntologyAwareTrait;

    const PROPERTIES = 'properties';
    const RANGES = 'ranges';

    /** @var core_kernel_classes_Class|null The class where the resource is */
    protected $class = null;

    /** @var core_kernel_classes_Resource|null The resource to manage */
    protected $resource = null;

    /** @var array Resource properties formatted for the form */
    protected $formProperties = [];

    /** @var array An array to store field ranges */
    protected $ranges = [];

    /**
     * tao_actions_form_RestForm constructor.
     *
     * Create a form for a given $instance
     * If $instance is a resource, it's an edition. The class is the resource class
     * Otherwise the $instance is a class where to create a new resource
     * Init the form itself
     *
     * @param $instance
     * @throws common_exception_NotFound
     * @throws common_Exception
     */
    public function __construct($instance)
    {
        if ($instance instanceof core_kernel_classes_Class) {
            $this->class = $instance;
        } elseif ($instance instanceof core_kernel_classes_Resource) {
            if (!$instance->exists()) {
                throw new common_exception_NotFound('Resource requested does not exist');
            }
            $this->resource = $instance;
            foreach ($instance->getTypes() as $type) {
                $this->class = $type;
                break;
            }
        }

        if (is_null($this->class)) {
            throw new common_Exception(__METHOD__ . ' requires a valid class');
        }

        $this->initFormProperties($this->getClassProperties());
    }

    /**
     * Initialize form properties.
     * - Only if resource property has a widget property
     * - Fetch property validator
     * - If necessary fetch property ranges
     * - In case of edition, retrieve property values
     * - Sort properties with GUI order property
     *
     * @param array $properties
     */
    public function initFormProperties(array $properties)
    {
        /** @var core_kernel_classes_Property $property */
        foreach ($properties as $property) {
            $property->feed();
            $widget = $this->getWidgetProperty($property);

            if (is_null($widget) || $widget instanceof core_kernel_classes_Literal) {
                continue;
            }

            $propertyData = [
                'uri' => $property->getUri(),
                'label' => $property->getLabel(),
                'widget' => $widget->getUri(),
            ];

            // Validators
            $validators = $this->getPropertyValidators($property);
            if (!empty($validators)) {
                $propertyData['validators'] = $validators;
            }

            // Range values
            /** @var core_kernel_classes_Class $range */
            $range = $property->getRange();
            if (!is_null($range) && $range->getUri() != 'http://www.w3.org/2000/01/rdf-schema#Literal') {
                $propertyData['range'] = $range->getUri();
                $this->ranges[$range->getUri()] = $this->getRangeData($range);
            }

            // Existing values
            if (
                $this->doesExist()
                && !is_null($value = $this->getFieldValue($property, isset($propertyData['range']) ? $propertyData['range'] : null))
            ) {
                $propertyData['value'] = $value;
            }

            // Field position in the form
            $guiPropertyOrder = $property->getOnePropertyValue($this->getProperty(TaoOntology::PROPERTY_GUI_ORDER));
            if (!is_null($guiPropertyOrder)) {

                $position = intval((string)$guiPropertyOrder);
                $propertyData['position'] = $position;
                $i = 0;
                while (
                    $i < count($this->formProperties)
                    && (isset($this->formProperties[$i]['position']) && $position >= $this->formProperties[$i]['position'])
                ) {
                    $i++;
                }
                array_splice($this->formProperties, $i, 0, array($propertyData));

            } else {
                $this->formProperties[] = $propertyData;
            }

        }
    }

    /**
     * Get the form data, properties and ranges formatted to escape uri
     *
     * @return array
     */
    public function getData()
    {
        return [
            self::PROPERTIES => $this->formProperties,
            self::RANGES => $this->ranges,
        ];
    }

    /**
     * Populate the form properties with given $parameters.
     *
     * @param array $parameters
     * @return $this
     */
    public function bind(array $parameters = [])
    {
        foreach ($this->formProperties as $key => $property) {
            if (isset($parameters[$property['uri']])) {
                $value = $parameters[$property['uri']];
            } else {
                $value = '';
            }
            $this->formProperties[$key]['formValue'] = $value;
        }
        return $this;
    }

    /**
     * Validate the form against the property validators.
     * In case of range, check if value belong to associated ranges list
     * Return failure report if one or more field is invalid
     *
     * @return common_report_Report
     * @throws common_Exception In case of runtime error
     */
    public function validate()
    {
        $report = common_report_Report::createInfo();

        foreach ($this->formProperties as $property) {

            try {
                $value = $property['formValue'];

                if (isset($property['validators'])) {
                    foreach ($property['validators'] as $validatorName) {
                        $validatorClass = 'tao_helpers_form_validators_' . $validatorName;
                        if (!class_exists($validatorClass)) {
                            throw new common_Exception('Validator is not correctly set (unknown)');
                        }
                        /** @var ValidatorInterface $validator */
                        $validator = new $validatorClass();
                        if (!$validator->evaluate($value)) {
                            throw new common_exception_ValidationFailed(
                                $property['uri'], $validator->getMessage()
                            );
                        }
                    }
                }

                if (isset($property['range'])) {
                    if (!isset($this->ranges[$property['range']])) {
                        throw new common_Exception($property['label'] . ' : Range is unknown');
                    }
                    $rangeValidated = false;
                    foreach ($this->ranges[$property['range']] as $rangeData) {

                        if (is_array($value)) {
                            foreach ($value as $k => $v) {
                                if ($rangeData['uri'] == $v) {
                                    unset($value[$k]);
                                }
                            }
                            if (empty($value)) {
                                $rangeValidated = true;
                                break;
                            }
                        } else {
                            if ($rangeData['uri'] == $value) {
                                $rangeValidated = true;
                                break;
                            }
                        }

                    }
                    if (!$rangeValidated) {
                        throw new common_exception_ValidationFailed(
                            $property['uri'], 'Range "' . $value . '" for field "' . $property['label'] . '" is not recognized.'
                        );
                    }

                }
            } catch (common_exception_ValidationFailed $e) {
                $subReport = common_report_Report::createFailure($e->getMessage());
                $subReport->setData($property['uri']);
                $report->add($subReport);
            }
        }

        return $report;
    }

    /**
     * Save the form resource.
     * If $resource is set, use GenerisFormDataBinder to update resource with form properties
     * If only $class is set, create a new resource under it
     *
     * @return core_kernel_classes_Resource|null
     * @throws common_Exception
     */
    public function save()
    {
        $values = $this->prepareValuesToSave();

        if ($this->isNew()) {
            if (!$resource = $this->class->createInstanceWithProperties($values)) {
                throw new common_Exception(__('Unable to save resource.'));
            }
            $this->resource = $resource;
        } else {
            $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($this->resource);
            $binder->bind($values);
        }

        return $this->resource;
    }

    /**
     * Get the class properties from GenersFormFactory
     *
     * @return array
     */
    protected function getClassProperties()
    {
        return array_merge(
            tao_helpers_form_GenerisFormFactory::getDefaultProperties(),
            tao_helpers_form_GenerisFormFactory::getClassProperties($this->class, $this->getTopClass())
        );
    }

    /**
     * Get the class associated to current form
     *
     * @return core_kernel_classes_Class
     */
    protected function getTopClass()
    {
        return $this->getClass(TaoOntology::CLASS_URI_OBJECT );
    }

    /**
     * Get the the property widget property
     *
     * @param core_kernel_classes_Property $property
     * @return core_kernel_classes_Property
     */
    protected function getWidgetProperty(core_kernel_classes_Property $property)
    {
        return $property->getWidget();
    }

    /**
     * Get property validators
     *
     * @param core_kernel_classes_Property $property
     * @return array
     */
    protected function getPropertyValidators(core_kernel_classes_Property $property)
    {
        $validators = [];
        /** @var ValidatorInterface $validator */
        foreach (ValidationRuleRegistry::getRegistry()->getValidators($property) as $validator) {
            $validators[] = $validator->getName();
        }
        return $validators;
    }

    /**
     * Get the list of resource associated to the given $range
     *
     * @param core_kernel_classes_Class $range
     * @return array
     */
    protected function getRangeData(core_kernel_classes_Class $range)
    {
        if (is_null($range) || $range instanceof core_kernel_classes_Literal) {
            return [];
        }

        $options = array();

        /** @var core_kernel_classes_Resource $rangeInstance */
        foreach ($range->getInstances(true) as $rangeInstance) {
            $options[] = [
                'uri' => $rangeInstance->getUri(),
                'label' => $rangeInstance->getLabel(),
            ];
        }

        if (!empty($options)) {
            ksort($options);
            return $options;
        }

        return [];
    }

    /**
     * Get the value of the given property.
     * If $range is not null, return the value $uri
     *
     * @param core_kernel_classes_Property $property
     * @param null $range
     * @return array|mixed|null
     */
    protected function getFieldValue(core_kernel_classes_Property $property, $range = null)
    {
        $propertyValues = [];

        /** @var core_kernel_classes_Resource $resource */
        $values = $this->resource->getPropertyValuesCollection($property);
        foreach ($values->getIterator() as $value) {

            if (is_null($value)) {
                continue;
            }

            if ($value instanceof core_kernel_classes_Resource) {
                if (!is_null($range)) {
                    $propertyValues[] = $value->getUri();
                } else {
                    $propertyValues[] = $value->getLabel();
                }
            } elseif ($value instanceof core_kernel_classes_Literal) {
                $propertyValues[] = (string)$value;
            }

        }

        if (!empty($propertyValues)) {
            if (count($propertyValues) == 1) {
                return $propertyValues[0];
            } else {
                return $propertyValues;
            }
        }

        return null;
    }

    /**
     * Check if current form exists
     *
     * @return bool
     */
    protected function doesExist()
    {
        return !is_null($this->resource);
    }

    /**
     * Check if current form is does not exist
     *
     * @return bool
     */
    protected function isNew()
    {
        return is_null($this->resource);
    }


    /**
     * Format the form properties to save them
     *
     * @return array
     */
    protected function prepareValuesToSave()
    {
        $values = [];
        foreach ($this->formProperties as $property) {
            $values[$property['uri']] = $property['formValue'];
        }
        return $values;
    }

}
