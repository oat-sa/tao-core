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

namespace oat\tao\test\user\import;

use oat\tao\model\user\import\UserCsvImporterFactory;
use oat\tao\model\user\import\UserImportServiceInterface;

class UserCsvImporterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetImporterDefined()
    {
        /** @var UserCsvImporterFactory $service */
        $service = $this->getMockBuilder(UserCsvImporterFactory::class)->disableOriginalConstructor()->setMethods(['buildService','getOption'])->getMock();

        $service
            ->method('buildService')
            ->willReturn($this->getMockForAbstractClass(UserImportServiceInterface::class));

        $service->expects($this->any())
            ->method('getOption')
            ->will($this->returnCallback(function ($prop){
                switch ($prop){
                    case 'mappers':
                        return array(
                            'test-taker' => array(
                                'importer' => $this->getMockForAbstractClass(UserImportServiceInterface::class)
                            ),
                            'proctor' => array(
                                'importer' => $this->getMockForAbstractClass(UserImportServiceInterface::class)
                            ),
                            'test-center-admin' => array(
                                'importer' => $this->getMockForAbstractClass(UserImportServiceInterface::class)
                            )
                        );
                        break;
                    case'default-schema':

                        return array(
                            'mandatory' => array(
                                'label' => 'http://www.w3.org/2000/01/rdf-schema#label',
                            ),
                            'optional' => array(
                                'default language' => 'http://www.tao.lu/Ontologies/generis.rdf#userDefLg',
                            )
                        );
                        break;
                }
            }));


        $this->assertInstanceOf(UserImportServiceInterface::class, $service->getImporter('test-taker'));
        $this->assertInstanceOf(UserImportServiceInterface::class, $service->getImporter('proctor'));
        $this->assertInstanceOf(UserImportServiceInterface::class, $service->getImporter('test-center-admin'));
    }
}
