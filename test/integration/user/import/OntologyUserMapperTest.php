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
use helpers_PasswordHash;
use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\tao\model\import\service\MandatoryFieldException;
use oat\tao\model\user\import\OntologyUserMapper;
use tao_models_classes_LanguageService;

class OntologyUserMapperTest extends GenerisPhpUnitTestRunner
{
    /**
     * @dataProvider provideBasicExample
     * @param $schema
     * @param $data
     * @param $expected
     * @throws \Exception
     */
    public function testMapUserWithSuccess($schema, $data, $expected)
    {
        $mapper = $this->getMapper();
        $mapper->setOption(OntologyUserMapper::OPTION_SCHEMA, $schema);

        $mapper->map($data)->combine(['extraProperty' => 'extraProperty']);

        $this->assertFalse($mapper->isEmpty());
        $this->assertSame('password', $mapper->getPlainPassword());

        $this->assertEquals($expected, $mapper->getProperties());
    }

    /**
     * @param $schema
     * @param $data
     * @param $expected
     *
     * @dataProvider provideInsufficientDataExample
     *
     * @throws \Exception
     */
    public function testMapMandatoryShouldFail($schema, $data, $expected)
    {
        $this->expectException(MandatoryFieldException::class);
        $mapper = $this->getMapper();
        $mapper->setOption(OntologyUserMapper::OPTION_SCHEMA, $schema);

        $mapper->map($data);
    }

    /**
     * @param $schema
     * @param $data
     * @param $expected
     *
     * @dataProvider provideEmptyFieldDataExample
     * @expectedException \oat\tao\model\import\service\MandatoryFieldException
     *
     * @throws \Exception
     */
    public function testMapMandatoryNotEmptyShouldFail($schema, $data, $expected)
    {
        $mapper = $this->getMapper();
        $mapper->setOption(OntologyUserMapper::OPTION_SCHEMA, $schema);

        $mapper->map($data);
    }

    /**
     * @return OntologyUserMapper
     */
    protected function getMapper()
    {
        $mapper = $this->getMockBuilder(OntologyUserMapper::class)
            ->setMethods(['getPasswordHashService', 'getLanguageService'])
            ->getMock();

        $passwordHashService = $this->getMockBuilder(helpers_PasswordHash::class)->disableOriginalConstructor()->getMock();
        $passwordHashService->method('encrypt')->willReturn('encrypted_password');

        $langResource = $this->getMockBuilder(core_kernel_classes_Resource::class)->disableOriginalConstructor()->getMock();
        $langResource->method('getUri')->willReturn('http://www.tao.lu/Ontologies/TAO.rdf#Langda-EN');

        $languageService = $this->getMockBuilder(tao_models_classes_LanguageService::class)->disableOriginalConstructor()->getMock();
        $languageService->method('getLanguageByCode')->willReturn($langResource);

        $mapper->method('getPasswordHashService')
            ->willReturn($passwordHashService);
        $mapper->method('getLanguageService')
            ->willReturn($languageService);

        return $mapper;
    }

    /**
     * @return array
     */
    public function provideBasicExample()
    {
        return [
            [
                'schema' => [
                    'mandatory' => array(
                        'label' => 'http://www.w3.org/2000/01/rdf-schema#label',
                        'interface language' => 'http://www.tao.lu/Ontologies/generis.rdf#userUILg',
                        'login' => 'http://www.tao.lu/Ontologies/generis.rdf#login',
                        'roles' => 'http://www.tao.lu/Ontologies/generis.rdf#userRoles',
                        'password' => 'http://www.tao.lu/Ontologies/generis.rdf#password'
                    ),
                    'optional' => array(
                        'interface language' => 'http://www.tao.lu/Ontologies/generis.rdf#userDefLg',
                        'first name' => 'http://www.tao.lu/Ontologies/generis.rdf#userFirstName',
                        'last name' => 'http://www.tao.lu/Ontologies/generis.rdf#userLastName',
                        'mail' => 'http://www.tao.lu/Ontologies/generis.rdf#userMail'
                    )
                ],
                'data' => [
                    'label' => 'user label',
                    'interface language' => 'en',
                    'login' => 'userlogin',
                    'password' => 'password',
                    'first name' => 'user first',
                    'last name' => 'user last',
                    'roles' => ['role1'],
                    'mail' => 'user@email.com',
                ],
                'result' => [
                    'http://www.w3.org/2000/01/rdf-schema#label' => 'user label',
                    'http://www.tao.lu/Ontologies/generis.rdf#userUILg' => 'http://www.tao.lu/Ontologies/TAO.rdf#Langda-EN',
                    'http://www.tao.lu/Ontologies/generis.rdf#login' => 'userlogin',
                    'http://www.tao.lu/Ontologies/generis.rdf#userRoles' => ['role1'],
                    'http://www.tao.lu/Ontologies/generis.rdf#password' => 'encrypted_password',
                    'http://www.tao.lu/Ontologies/generis.rdf#userDefLg' => 'http://www.tao.lu/Ontologies/TAO.rdf#Langda-EN',
                    'http://www.tao.lu/Ontologies/generis.rdf#userFirstName' => 'user first',
                    'http://www.tao.lu/Ontologies/generis.rdf#userLastName' => 'user last',
                    'http://www.tao.lu/Ontologies/generis.rdf#userMail' => 'user@email.com',
                    'extraProperty' => 'extraProperty'
                ]
            ],
        ];
    }

    public function provideInsufficientDataExample()
    {
        return [
            [
                'schema' => [
                    'mandatory' => array(
                        'label' => 'http://www.w3.org/2000/01/rdf-schema#label',
                        'login' => 'http://www.tao.lu/Ontologies/generis.rdf#login',
                        'password' => 'http://www.tao.lu/Ontologies/generis.rdf#password',
                    )
                ],
                'data' => [
                    'login' => 'userlogin',
                ],
                'result' => [
                    'http://www.tao.lu/Ontologies/generis.rdf#login' => 'userlogin',
                    'http://www.tao.lu/Ontologies/generis.rdf#password' => 'encrypted_password',
                ]
            ],
        ];
    }

    public function provideEmptyFieldDataExample()
    {
        return [
            [
                'schema' => [
                    'mandatory' => array(
                        'label' => 'http://www.w3.org/2000/01/rdf-schema#label',
                        'login' => 'http://www.tao.lu/Ontologies/generis.rdf#login',
                        'password' => 'http://www.tao.lu/Ontologies/generis.rdf#password',
                    )
                ],
                'data' => [
                    'login' => 'userlogin',
                    'label' => '',
                    'password' => '',
                ],
                'result' => [
                    'http://www.tao.lu/Ontologies/generis.rdf#login' => 'userlogin',
                    'http://www.tao.lu/Ontologies/generis.rdf#password' => 'encrypted_password',
                ]
            ],
        ];
    }
}
