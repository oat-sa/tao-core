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
 */
namespace oat\test\model\service;

use oat\tao\test\TaoPhpUnitTestRunner;
/**
 *
 * @author Joel Bout <joel@taotesting.com>
 */
class ServiceCallTest extends TaoPhpUnitTestRunner {

    public function setUp()
    {
        parent::setUp();
        \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
    }

    public function testJson() {

        $serviceCall = new \tao_models_classes_service_ServiceCall('http://testcase/test#123');
        $json = json_encode($serviceCall);
        $serviceCall2 = \tao_models_classes_service_ServiceCall::fromJson(json_decode($json, true));
        $this->assertIsA($serviceCall2, \tao_models_classes_service_ServiceCall::class);
        $this->assertEquals($serviceCall, $serviceCall2);

        $serviceCall3 = new \tao_models_classes_service_ServiceCall('http://testcase/test#123');
        $serviceCall3->addInParameter(new \tao_models_classes_service_ConstantParameter
        (new \core_kernel_classes_Resource('http://testcase/test#123'), "v1"));
        $serviceCall3->addInParameter(new \tao_models_classes_service_ConstantParameter
        (new \core_kernel_classes_Resource('http://testcase/test#123'), "v2"));
        $serviceCall3->setOutParameter(new \tao_models_classes_service_VariableParameter(
            new \core_kernel_classes_Resource('http://testcase/test#123'), new \core_kernel_classes_Resource('http://testcase/test#123')));

        $json = json_encode($serviceCall3);
        $serviceCall4 = \tao_models_classes_service_ServiceCall::fromJson(json_decode($json, true));
        $this->assertIsA($serviceCall4, \tao_models_classes_service_ServiceCall::class);
        $this->assertEquals($serviceCall3, $serviceCall4);
    }

    public function testOntology() {
        $serviceCall = new \tao_models_classes_service_ServiceCall('http://testcase/test#123');
        $resource = $serviceCall->toOntology();
        $serviceCall2 = \tao_models_classes_service_ServiceCall::fromResource($resource);
        $this->assertIsA($serviceCall2, \tao_models_classes_service_ServiceCall::class);
        $this->assertEquals($serviceCall, $serviceCall2);

        $serviceCall3 = new \tao_models_classes_service_ServiceCall('http://testcase/test#123');
        $serviceCall3->addInParameter(new \tao_models_classes_service_ConstantParameter
        (new \core_kernel_classes_Resource('http://testcase/test#123'), "v1"));
        $serviceCall3->addInParameter(new \tao_models_classes_service_ConstantParameter
        (new \core_kernel_classes_Resource('http://testcase/test#123'), "v2"));
        $serviceCall3->setOutParameter(new \tao_models_classes_service_VariableParameter(
            new \core_kernel_classes_Resource('http://testcase/test#123'),  new \core_kernel_classes_Resource('http://testcase/test#123')));

        $resource = $serviceCall3->toOntology();
        $serviceCall4 = \tao_models_classes_service_ServiceCall::fromResource($resource);
        $this->assertIsA($serviceCall3, \tao_models_classes_service_ServiceCall::class);
        $serviceCall4Array = $serviceCall4->jsonSerialize();
        usort($serviceCall4Array['in'], function ($inParamA, $inParamB) {
            return strcasecmp($inParamA->getValue(), $inParamB->getValue());
        });
        $this->assertEquals($serviceCall3->jsonSerialize(), $serviceCall4Array);
    }
}
