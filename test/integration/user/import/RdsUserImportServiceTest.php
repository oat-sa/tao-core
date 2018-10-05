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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\test\integration\user\import;

use core_kernel_classes_Resource;
use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\tao\model\user\import\RdsUserImportService;
use oat\tao\model\user\import\UserMapperInterface;
use Psr\Log\NullLogger;

class RdsUserImportServiceTest extends GenerisPhpUnitTestRunner
{
    /**
     * @dataProvider provideMapperProperties
     * @param $data
     * @throws \Exception
     * @throws \common_exception_Error
     */
    public function testImport($data)
    {
        $importService = $this->getImportService($data);

        $report = $importService->import(__DIR__ . '/example.csv');

        $this->assertTrue($report->hasChildren());
        $this->assertSame(2, count($report->getSuccesses()));
        $this->assertSame(1, count($report->getErrors()));
    }

    /**
     * @dataProvider provideMapperProperties
     * @expectedException \Exception
     * @throws \Exception
     */
    public function testImportFileNotExists($data)
    {
        $importService = $this->getImportService($data);
        $importService->import(__DIR__ . '/not_existing_file.csv');
    }

    /**
     * @param $dataProvider
     * @return RdsUserImportService
     */
    protected function getImportService($dataProvider)
    {
        $importService = $this->getMockBuilder(RdsUserImportService::class)
            ->setMethods([
                'getClass',
                'triggerUserUpdated'
            ])
            ->getMockForAbstractClass();

        $resource = $this->getMockBuilder(core_kernel_classes_Resource::class)
            ->setMethods(['getUri', 'searchInstances', 'createInstanceWithProperties', 'removePropertyValues', 'editPropertyValues'])
            ->disableOriginalConstructor()
            ->getMock();
        $resource
            ->method('getUri')
            ->willReturn(rand(0, 1000));
        $resource
            ->method('createInstanceWithProperties')
            ->willReturn($resource);
        $resource
            ->method('removePropertyValues')
            ->willReturn(true);
        $resource
            ->method('editPropertyValues')
            ->willReturn(true);
        $resource
            ->method('searchInstances')
            ->will($this->onConsecutiveCalls(
                (count($dataProvider[0]['results']) > 0 ? [$resource] : []),
                (count($dataProvider[1]['results']) > 0 ? [$resource] : [])
            ));

        $importService
            ->method('getClass')
            ->willReturn($resource);
        $importService
            ->method('triggerUserUpdated')
            ->willReturn(true);

        $importService->setLogger(new NullLogger());

        $reportMock = $this->prophesize(\common_report_Report::class)->reveal();
        $mapper = $this->getMock(UserMapperInterface::class);
        $mapper->method('map')
            ->will($this->onConsecutiveCalls(
                $mapper,
                $mapper,
                $this->throwException(new \Exception())
            ));
        $mapper->method('getReport')
            ->willReturn($reportMock);
        $mapper->method('getPlainPassword')->willReturn('plainPassword');
        $mapper->method('getProperties')->will($this->onConsecutiveCalls(
            $dataProvider[0]['properties'],
            $dataProvider[1]['properties']
        ));

        $mapper->method('combine')->willReturn($mapper);
        $mapper->method('isEmpty')->willReturn(false);

        $importService->setMapper($mapper);

        return $importService;
    }

    public function provideMapperProperties()
    {
        return [
            [
                'mapper' => [
                    [
                        'results' => [],
                        'properties' => [
                            'http://www.w3.org/2000/01/rdf-schema#label' => 'user1',
                            'http://www.tao.lu/Ontologies/generis.rdf#userUILg' => 'http://www.tao.lu/Ontologies/TAO.rdf#Langda-EN',
                            'http://www.tao.lu/Ontologies/generis.rdf#login' => 'user1',
                            'http://www.tao.lu/Ontologies/generis.rdf#userRoles' => ['role1'],
                            'http://www.tao.lu/Ontologies/generis.rdf#password' => 'encrypted_password',
                            'http://www.tao.lu/Ontologies/generis.rdf#userDefLg' => 'http://www.tao.lu/Ontologies/TAO.rdf#Langda-EN',
                            'http://www.tao.lu/Ontologies/generis.rdf#userFirstName' => 'user first name1',
                            'http://www.tao.lu/Ontologies/generis.rdf#userLastName' => 'user last name1',
                            'http://www.tao.lu/Ontologies/generis.rdf#userMail' => 'user@mail.com1',],
                    ],
                    [
                        'results' => [ 'one'],
                        'properties' => [
                            'http://www.w3.org/2000/01/rdf-schema#label' => 'user2',
                            'http://www.tao.lu/Ontologies/generis.rdf#userUILg' => 'http://www.tao.lu/Ontologies/TAO.rdf#Langda-EN',
                            'http://www.tao.lu/Ontologies/generis.rdf#login' => 'user2',
                            'http://www.tao.lu/Ontologies/generis.rdf#userRoles' => ['role1'],
                            'http://www.tao.lu/Ontologies/generis.rdf#password' => 'encrypted_password',
                            'http://www.tao.lu/Ontologies/generis.rdf#userDefLg' => 'http://www.tao.lu/Ontologies/TAO.rdf#Langda-EN',
                            'http://www.tao.lu/Ontologies/generis.rdf#userFirstName' => 'user first name2',
                            'http://www.tao.lu/Ontologies/generis.rdf#userLastName' => 'user last name2',
                            'http://www.tao.lu/Ontologies/generis.rdf#userMail' => 'user@mail.com2',
                        ]
                    ]
                ]
            ]
        ];
    }
}
