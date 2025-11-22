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

namespace oat\tao\test\unit\user\import;

use oat\tao\model\import\service\ImportServiceInterface;
use oat\tao\model\user\import\UserCsvImporterFactory;
use PHPUnit\Framework\TestCase;

class UserCsvImporterFactoryTest extends TestCase
{
    public function testGetImporterDefined()
    {
        /** @var UserCsvImporterFactory $factory */
        $factory = $this->getMockBuilder(UserCsvImporterFactory::class)->disableOriginalConstructor()
            ->onlyMethods(['buildService','getOption', 'propagate'])->getMock();

        $factory
            ->method('buildService')
            ->willReturn($this->getMockForAbstractClass(ImportServiceInterface::class));

        $factory->expects($this->any())
            ->method('getOption')
            ->will($this->returnCallback(function ($prop) {
                switch ($prop) {
                    case 'mappers':
                        return [
                            'test-taker' => [
                                'importer' => $this->getMockForAbstractClass(ImportServiceInterface::class)
                            ],
                            'proctor' => [
                                'importer' => $this->getMockForAbstractClass(ImportServiceInterface::class)
                            ],
                            'test-center-admin' => [
                                'importer' => $this->getMockForAbstractClass(ImportServiceInterface::class)
                            ]
                        ];
                        break;
                    case 'default-schema':
                        return [
                            'mandatory' => [
                                'label' => 'http://www.w3.org/2000/01/rdf-schema#label',
                            ],
                            'optional' => [
                                'default language' => 'http://www.tao.lu/Ontologies/generis.rdf#userDefLg',
                            ]
                        ];
                        break;
                }
            }));


        $this->assertInstanceOf(ImportServiceInterface::class, $factory->create('test-taker'));
        $this->assertInstanceOf(ImportServiceInterface::class, $factory->create('proctor'));
        $this->assertInstanceOf(ImportServiceInterface::class, $factory->create('test-center-admin'));
    }
}
