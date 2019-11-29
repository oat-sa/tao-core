<?php

namespace oat\tao\test\models\classes\service;

use InvalidArgumentException;
use tao_models_classes_service_Parameter;
use tao_models_classes_service_ServiceCall;
use tao_models_classes_service_VariableParameter;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;

class tao_models_classes_service_ServiceCallTest extends TestCase
{
    const SERVICE_DEFINITION = 'DEFINITION';

    /**
     * @var tao_models_classes_service_ServiceCall;
     */
    private $object;

    /**
     * @var tao_models_classes_service_Parameter|MockObject
     */
    private $inputParam;

    /**
     * @var tao_models_classes_service_VariableParameter|MockObject
     */
    private $outputParam;

    protected function setUp()
    {
        parent::setUp();

        $serializedInput = ['inKey' => 'inValue'];
        $serializedOutput = ['outKey' => 'outValue'];

        $this->inputParam = $this->createMock(tao_models_classes_service_Parameter::class);
        $this->inputParam->method('jsonSerialize')
            ->willReturn($serializedInput);

        $this->outputParam = $this->createMock(tao_models_classes_service_VariableParameter::class);
        $this->outputParam->method('jsonSerialize')
            ->willReturn($serializedOutput);

        $this->object = new tao_models_classes_service_ServiceCall(self::SERVICE_DEFINITION);
        $this->object->addInParameter($this->inputParam);
        $this->object->setOutParameter($this->outputParam);
    }

    public function testSerializeToString()
    {
        $expectedResult = '{"service":"DEFINITION","in":[{"inKey":"inValue"}],"out":{"outKey":"outValue"}}';

        $result = $this->object->serializeToString();
        $this->assertEquals($expectedResult, $result, 'Serialized ServiceCall JSON string must be as expected.');

        $resultJsonEncode = json_encode($this->object);
        $this->assertEquals($expectedResult, $resultJsonEncode, 'ServiceCall serialized by using json_encode() must be as expected.');
    }

    public function testFromJson()
    {
        $serviceCallData = [
            "service" => "DEFINITION",
            "in" => [
                [
                    "const" => "constValue",
                    "def" => "defValue"
                ],
                [
                    "proc" => "procValue",
                    "def" => "defValue"
                ],
            ],
            "out" => [
                "proc" => "procValue",
                "def" => "defValue"
            ],
        ];

        $result = tao_models_classes_service_ServiceCall::fromJson($serviceCallData);
        $this->assertInstanceOf(tao_models_classes_service_ServiceCall::class, $result, 'ServiceCall object created from array must be an instance of tao_models_classes_service_ServiceCall class.');
    }

    public function testFromStringInvalidJsonThrowsException()
    {
        $serviceCallJson = 'INVALID JSON STRING';

        $this->expectException(InvalidArgumentException::class);
        tao_models_classes_service_ServiceCall::fromString($serviceCallJson);
    }

    public function testFromString()
    {
        $serviceCallJson = '{"service":"DEFINITION","in":[{"const":"constValue","def":"defValue"},{"proc":"procValue","def":"defValue"}],"out":{"proc":"procValue","def":"defValue"}}';

        $result = tao_models_classes_service_ServiceCall::fromString($serviceCallJson);
        $this->assertInstanceOf(tao_models_classes_service_ServiceCall::class, $result, 'ServiceCall object created from JSON string must be an instance of tao_models_classes_service_ServiceCall class.');
    }

    public function testJsonSerialize()
    {
        $expectedResult = [
            'service' => self::SERVICE_DEFINITION,
            'in' => [$this->inputParam],
            'out' => $this->outputParam,
        ];

        $result = $this->object->jsonSerialize();
        $this->assertEquals($expectedResult, $result, 'Serialized ServiceCall array must be as expected.');
    }
}
