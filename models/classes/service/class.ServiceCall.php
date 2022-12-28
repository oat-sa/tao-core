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
 * Copyright (c) 2013-2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\WfEngineOntology;

/**
 * Represents a call of an interactive tao service
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class tao_models_classes_service_ServiceCall implements JsonSerializable
{
    use OntologyAwareTrait;

    private ?core_kernel_classes_Resource $serviceDefinitionId;
    private array $inParameters = [];
    private ?tao_models_classes_service_VariableParameter $outParameter = null;

    public function __construct($serviceDefinition)
    {
        $this->serviceDefinitionId = is_object($serviceDefinition)
           ? $serviceDefinition->getUri()
           : $serviceDefinition;
    }

    public function addInParameter(tao_models_classes_service_Parameter $param): void
    {
        $this->inParameters[] = $param;
    }

    public function setOutParameter(tao_models_classes_service_VariableParameter $param)
    {
        $this->outParameter = $param;
    }

    public function getServiceDefinitionId(): core_kernel_classes_Resource
    {
        return $this->serviceDefinitionId;
    }

    public function getInParameters(): array
    {
        return $this->inParameters;
    }

    public function getRequiredVariables(): array
    {
        $variables = [];
        foreach ($this->inParameters as $param) {
            if ($param instanceof tao_models_classes_service_VariableParameter) {
                $variables[] = $param->getVariable();
            }
        }
        return $variables;
    }

    public function toOntology(): core_kernel_classes_Resource
    {
        $inResources = [];
        $outResources = is_null($this->outParameter)
           ? []
           : $this->outParameter->toOntology($this->getModel());
        foreach ($this->inParameters as $param) {
            $inResources[] = $param->toOntology($this->getModel());
        }
        $serviceCallClass = $this->getClass(WfEngineOntology::CLASS_URI_CALL_OF_SERVICES);
        return $serviceCallClass->createInstanceWithProperties([
            OntologyRdfs::RDFS_LABEL => 'serviceCall',
            WfEngineOntology::PROPERTY_CALL_OF_SERVICES_SERVICE_DEFINITION    => $this->serviceDefinitionId,
            WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_IN    => $inResources,
            WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_OUT   => $outResources,
            WfEngineOntology::PROPERTY_CALL_OF_SERVICES_WIDTH                => '100',
            WfEngineOntology::PROPERTY_CALL_OF_SERVICES_HEIGHT               => '100'
        ]);
    }

    /**
     * @throws common_exception_InconsistentData
     * @throws common_exception_InvalidArgumentType
     */
    public static function fromResource(core_kernel_classes_Resource $resource): tao_models_classes_service_ServiceCall
    {
        $values = $resource->getPropertiesValues([
            WfEngineOntology::PROPERTY_CALL_OF_SERVICES_SERVICE_DEFINITION,
            WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_IN,
            WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_OUT
        ]);
        $serviceDefUri = current($values[WfEngineOntology::PROPERTY_CALL_OF_SERVICES_SERVICE_DEFINITION]);
        $serviceCall = new self($serviceDefUri);
        foreach ($values[WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_IN] as $inRes) {
            $param = tao_models_classes_service_Parameter::fromResource($inRes);
            $serviceCall->addInParameter($param);
        }
        if (!empty($values[WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_OUT])) {
            $param = tao_models_classes_service_Parameter::fromResource(
                current($values[WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_OUT])
            );
            $serviceCall->setOutParameter($param);
        }
        return $serviceCall;
    }

    /**
     * @deprecated Use json_encode($serviceCall) instead
     */
    public function serializeToString(): string
    {
        return json_encode($this);
    }

    /**
     * Unserialize the string to a serivceCall object
     */
    public static function fromString(string $string): tao_models_classes_service_ServiceCall
    {
        $data = json_decode($string, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Provided string is not a valid JSON.");
        }

        return self::fromJson($data);
    }

    public function jsonSerialize(): array
    {
        return [
            'service' => $this->serviceDefinitionId,
            'in' => $this->inParameters,
            'out' => $this->outParameter
        ];
    }

    public static function fromJson(array $data): tao_models_classes_service_ServiceCall
    {
        $call = new self($data['service']);
        if (!empty($data['out'])) {
            $call->setOutParameter(tao_models_classes_service_Parameter::fromJson($data['out']));
        }
        foreach ($data['in'] as $in) {
            $call->addInParameter(tao_models_classes_service_Parameter::fromJson($in));
        }
        return $call;
    }
}
