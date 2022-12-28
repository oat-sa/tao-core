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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

use oat\tao\model\WfEngineOntology;
use oat\generis\model\data\Ontology;

/**
 * Represents tao service parameter
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * phpcs:disable Squiz.Classes.ValidClassName
 */
abstract class tao_models_classes_service_Parameter implements JsonSerializable
{
    private core_kernel_classes_Resource $definition;

    public function __construct(core_kernel_classes_Resource $definition)
    {
        $this->definition = $definition;
    }

    /**
     * Returns the formal definition of this parameter
     */
    public function getDefinition(): core_kernel_classes_Resource
    {
        return $this->definition;
    }

    abstract public function toOntology(Ontology $model): core_kernel_classes_Resource;

    abstract public function jsonSerialize(): array;

    public static function fromJson($data)
    {
        if (isset($data['const'])) {
            $param = new tao_models_classes_service_ConstantParameter(
                new core_kernel_classes_Resource($data['def']),
                $data['const']
            );
        } else {
            $param = new tao_models_classes_service_VariableParameter(
                new core_kernel_classes_Resource($data['def']),
                new core_kernel_classes_Resource($data['proc'])
            );
        }
        return $param;
    }

    /**
     * Builds a service call parameter from it's serialized form
     * @throws common_exception_InconsistentData
     * @throws common_exception_InvalidArgumentType
     */
    public static function fromResource(core_kernel_classes_Resource $resource): tao_models_classes_service_Parameter
    {
        $values = $resource->getPropertiesValues([
            WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_FORMAL_PARAMETER,
            WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_CONSTANT_VALUE,
            WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_PROCESS_VARIABLE
        ]);

        if (count($values[WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_FORMAL_PARAMETER]) != 1) {
            throw new common_exception_InconsistentData(
                'Actual variable ' . $resource->getUri() . ' missing formal parameter'
            );
        }

        $countConstValue = count($values[WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_CONSTANT_VALUE]);
        $countProcessVariable = count($values[WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_PROCESS_VARIABLE]);

        if ($countConstValue + $countProcessVariable != 1) {
            throw new common_exception_InconsistentData('Actual variable ' . $resource->getUri() . ' invalid, '
                . $countConstValue . ' constant values and '
                . $countProcessVariable . ' process variables');
        }

        if (count($values[WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_CONSTANT_VALUE]) > 0) {
            $param = new tao_models_classes_service_ConstantParameter(
                current($values[WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_FORMAL_PARAMETER]),
                current($values[WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_CONSTANT_VALUE])
            );
        } else {
            $param = new tao_models_classes_service_VariableParameter(
                current($values[WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_FORMAL_PARAMETER]),
                current($values[WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_PROCESS_VARIABLE])
            );
        }

        return $param;
    }
}
