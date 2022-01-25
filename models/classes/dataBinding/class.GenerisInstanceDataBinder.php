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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2022 (update and modification) Open Assessment Technologies SA;
 */

use oat\generis\model\OntologyRdf;
use oat\tao\model\dataBinding\AbstractDataBinder;
use oat\tao\model\event\MetadataModified;
use oat\oatbox\event\EventManager;
use oat\oatbox\service\ServiceManager;

/**
 * A data binder focusing on binding a source of data to a Generis instance.
 *
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 */
class tao_models_classes_dataBinding_GenerisInstanceDataBinder extends AbstractDataBinder
{
    /** @var core_kernel_classes_Resource */
    private $targetInstance;

    /** @var EventManager */
    private $eventManager;

    /**
     * Creates a new instance of binder.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function __construct(core_kernel_classes_Resource $targetInstance)
    {
        $this->targetInstance = $targetInstance;
    }

    public function withEventManager(EventManager $eventManager): void
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Returns the target instance.
     *
     * @access protected
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return core_kernel_classes_Resource
     */
    protected function getTargetInstance(): core_kernel_classes_Resource
    {
        return $this->targetInstance;
    }

    /**
     * Bind data from the source to a specific Generis class instance.
     *
     * The array of the data to be bound must contain keys that are property
     * The respective values can be either scalar or vector (array) values or
     * values.
     *
     * - If the element of the $data array is scalar, it is simply bound using
     * - If the element of the $data array is a vector, the property values are
     *   with the values of the vector.
     *
     * @access public
     * @param array data An array of values where keys are Property URIs and
     *                    values are either scalar or vector values.
     *
     * @return core_kernel_classes_Resource
     * @throws Exception
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     */
    public function bind($data)
    {
        $instance = $this->getTargetInstance();

        try {
            foreach ($data as $propertyUri => $value) {
                if ($propertyUri === OntologyRdf::RDF_TYPE && null !== $value) {
                    $this->bindTypes($instance, $value);
                } else {
                    $this->bindProperty($instance, $propertyUri, $value);
                    $this->getEventManager()->trigger(
                        new MetadataModified($instance, $propertyUri, $value)
                    );
                }
            }

            return $instance;
        } catch (Throwable $error) {
            throw new tao_models_classes_dataBinding_GenerisInstanceDataBindingException(
                $this->getTargetInstance(),
                $error->getMessage(),
                0,
                $error
            );
        }
    }

    private function bindTypes(
        core_kernel_classes_Resource $instance,
        $propertyValue
    ): void {
        foreach ($instance->getTypes() as $type) {
            $instance->removeType($type);
        }

        $types = !is_array($propertyValue) ? [$propertyValue] : $propertyValue;

        foreach ($types as $type) {
            $instance->setType($instance->getClass($type));
        }
    }

    private function bindProperty(
        core_kernel_classes_Resource $instance,
        string $propertyUri,
        $newValue
    ): void {
        $prop = $instance->getProperty($propertyUri);
        $values = $instance->getPropertyValuesCollection($prop);

        if ($values->count() > 0) {
            $this->bindPropertyWithPreviousValues($instance, $prop, $newValue);
        } elseif (is_array($newValue)) {
            foreach ($newValue as $aPropertyValue) {
                $instance->setPropertyValue($prop, $aPropertyValue);
            }
        } elseif (is_string($newValue) && !$this->isEmptyValue($newValue)) {
            $instance->setPropertyValue($prop, $newValue);
        }
    }

    private function bindPropertyWithPreviousValues(
        core_kernel_classes_Resource $instance,
        core_kernel_classes_Property $property,
        $propertyValue
    ): void {
        if (is_array($propertyValue)) {
            $instance->removePropertyValues($property);

            foreach ($propertyValue as $aPropertyValue) {
                $instance->setPropertyValue($property, $aPropertyValue);
            }
        } elseif (is_string($propertyValue)) {
            if ($this->isEmptyValue($propertyValue)) {
                // Setting an empty value for a scalar property deletes
                // the statement
                $instance->removePropertyValues($property);
            } else {
                $instance->editPropertyValues($property, $propertyValue);
            }
        }
    }

    private function isEmptyValue(string $value): bool
    {
        return '' === $value || ' ' === $value || strlen(trim($value)) == 0;
    }

    private function getEventManager(): EventManager
    {
        if ($this->eventManager === null) {
            $this->eventManager = ServiceManager::getServiceManager()->get(EventManager::SERVICE_ID);
        }

        return $this->eventManager;
    }
}
