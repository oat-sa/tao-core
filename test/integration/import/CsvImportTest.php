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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\tao\test\TaoPhpUnitTestRunner;
use oat\tao\model\import\CsvBasicImporter;
use Prophecy\Argument;

class CsvImportTest extends TaoPhpUnitTestRunner
{
    public const CSV_FILE_USERS_HEADER_UNICODE = '/../samples/csv/users1-header.csv';

    public function testImport()
    {
        $importer = new CsvBasicImporter();
        $staticMap = [];
        $map = [];

        $file = $this->getTempFileToUpload('csv/users1-header.csv');

        $resource = $this->prophesize(core_kernel_classes_Resource::class);
        $class = $this->prophesize(core_kernel_classes_Class::class);
        $class->createInstanceWithProperties($staticMap)->willReturn($resource->reveal());

        $options = ['file' => $file, 'map' => $map, 'staticMap' => $staticMap];
        $report = $importer->import($class->reveal(), $options);

        $this->assertInstanceOf('common_report_Report', $report);
        $this->assertEquals(16, count($report->getSuccesses()));
        $this->assertEquals(common_report_Report::TYPE_SUCCESS, $report->getType());
        $this->assertFalse($file->exists());
    }

    public function testCsvMapping()
    {
        $importer = new CsvBasicImporter();

        $expectedHeaderMap = ['label','First Name','Last Name','Login','Mail','password','UserUILg'];

        $labelProperty = new \core_kernel_classes_Property(OntologyRdfs::RDFS_LABEL);

        $property1 = $this->prophesize('\core_kernel_classes_Property');
        $property1->getUri()->willReturn('uriproperty1');
        $property1->getLabel()->willReturn('lAst naMe');

        $property2 = $this->prophesize('\core_kernel_classes_Property');
        $property2->getUri()->willReturn('uriproperty2');
        $property2->getLabel()->willReturn('Login');

        $property3 = $this->prophesize('\core_kernel_classes_Property');
        $property3->getUri()->willReturn('http://tao.unit/test.rdf#fIrstnAmE');
        $property3->getLabel()->willReturn('labelproperty3');

        $property4 = $this->prophesize('\core_kernel_classes_Property');
        $property4->getUri()->willReturn('http://tao.unit/test.rdf#email');
        $property4->getLabel()->willReturn('labelproperty4');

        $properties = [
            $labelProperty,
            $property1->reveal(),
            $property2->reveal(),
            $property3->reveal(),
            $property4->reveal()
        ];

        $class = $this->prophesize('\core_kernel_classes_Class');
        $class->getProperties(false)->willReturn($properties);
        $class->getUri()->willReturn(GenerisRdf::CLASS_GENERIS_RESOURCE);

        // Copy file because it should be removed
        $path = dirname(__FILE__) . self::CSV_FILE_USERS_HEADER_UNICODE;

        $options[\tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES] = true;
        $map = $importer->getCsvMapping($class->reveal(), $path, $options);

        $this->assertArrayHasKey('classProperties', $map);
        $this->assertArrayHasKey('headerList', $map);
        $this->assertArrayHasKey('mapping', $map);
        $this->assertEquals($expectedHeaderMap, $map['headerList']);
        $this->assertCount(5, $map['classProperties']);
        $this->assertArrayHasKey(OntologyRdfs::RDFS_LABEL, $map['classProperties']);
        $this->assertArrayHasKey('uriproperty1', $map['classProperties']);
        $this->assertArrayHasKey('uriproperty2', $map['classProperties']);
        $this->assertArrayHasKey('http://tao.unit/test.rdf#fIrstnAmE', $map['classProperties']);
        $this->assertArrayHasKey('http://tao.unit/test.rdf#email', $map['classProperties']);
        $this->assertEquals('Label', $map['classProperties'][OntologyRdfs::RDFS_LABEL]);
        $this->assertEquals('lAst naMe', $map['classProperties']['uriproperty1']);
        $this->assertEquals('Login', $map['classProperties']['uriproperty2']);
        $this->assertEquals('labelproperty3', $map['classProperties']['http://tao.unit/test.rdf#fIrstnAmE']);
        $this->assertEquals('labelproperty4', $map['classProperties']['http://tao.unit/test.rdf#email']);
        $this->assertCount(5, $map['mapping']);
        $this->assertEquals(0, $map['mapping'][OntologyRdfs::RDFS_LABEL]);
        $this->assertEquals(2, $map['mapping']['uriproperty1']);
        $this->assertEquals(3, $map['mapping']['uriproperty2']);
        $this->assertEquals(1, $map['mapping']['http://tao.unit/test.rdf#fIrstnAmE']);
        $this->assertEquals(4, $map['mapping']['http://tao.unit/test.rdf#email']);
    }

    public function testGetDataSample()
    {
        $importer = new CsvBasicImporter();

        $path = dirname(__FILE__) . self::CSV_FILE_USERS_HEADER_UNICODE;
        $expectedKeys = ['label','First Name','Last Name','Login','Mail','password','UserUILg'];
        $expectedLogin = ['jbogaerts','pplichart','rjadoul','chenri','ijars'];

        $sample = $importer->getDataSample($path);

        $this->assertCount(5, $sample);
        foreach ($sample as $i => $row) {
            $this->assertCount(7, $row);
            $this->assertEquals($expectedKeys, array_keys($row));
            $this->assertEquals($expectedLogin[$i], $row['Login']);
        }

        $expectedKeys = [0,1,2,3,4,5,6];
        $sample = $importer->getDataSample($path, [], 20, false);

        $this->assertCount(16, $sample);
        foreach ($sample as $i => $row) {
            $this->assertCount(7, $row);
            $this->assertEquals($expectedKeys, array_keys($row));
        }
    }

    public function testGetColumnMapping()
    {
        $file = dirname(__FILE__) . self::CSV_FILE_USERS_HEADER_UNICODE;
        $importer = new CsvBasicImporter();
        $class = new ReflectionClass('oat\\tao\\model\\import\\CsvBasicImporter');
        $method = $class->getMethod('getColumnMapping');
        $method->setAccessible(true);
        $csv_data = new \tao_helpers_data_CsvFile();
        $csv_data->load($file);
        $map = $method->invokeArgs($importer, [$csv_data, false]);

        $this->assertEquals(array_fill(0, 7, null), $map);

        $expectedHeader = ['label','First Name','Last Name','Login','Mail','password','UserUILg'];
        $map = $method->invokeArgs($importer, [$csv_data, true]);
        $this->assertEquals($expectedHeader, $map);
    }

    public function testGetClassProperties()
    {
        $importer = new CsvBasicImporter();
        $class = new ReflectionClass('oat\\tao\\model\\import\\CsvBasicImporter');
        $method = $class->getMethod('getClassProperties');
        $method->setAccessible(true);

        $property1 = new \core_kernel_classes_Property('testProperty1');
        $property2 = new \core_kernel_classes_Property('testProperty2');
        $property3 = new \core_kernel_classes_Property('testProperty3');

        $propertiesExpected = [$property1, $property2, $property3];
        $clazz = $this->prophesize('\core_kernel_classes_Class');
        $clazz->getUri()->willReturn(GenerisRdf::CLASS_GENERIS_RESOURCE);
        $clazz->getProperties(false)->willReturn($propertiesExpected);
        $properties = $method->invokeArgs($importer, [$clazz->reveal()]);

        $this->assertEquals($propertiesExpected, $properties);
    }

    public function testImportRules()
    {
        $file = $this->getTempFileToUpload('csv/users1-header-rules-validator.csv');

        $importer = new CsvBasicImporter();

        $class = $this->prophesize('\core_kernel_classes_Class');
        $resource = $this->prophesize('\core_kernel_classes_Resource');

        $class->createInstanceWithProperties([
            "label" => ["Correct row"],
            "firstName" => ["Jérôme"],
            "lastName" => ["Bogaerts"],
            "login" => ["jbogaerts"],
            "mail" => ["jerome.bogaerts@tudor.lu"],
            "password" => ["jbogaerts!!!111Ok"],
            "UserUIlg" => ["http://www.tao.lu/Ontologies/TAO.rdf#LangEN"]
        ])
            ->shouldBeCalledTimes(1)
            ->willReturn($resource->reveal());

        $importer->setValidators([
            'label' => [
                tao_helpers_form_FormFactory::getValidator('Length', ["max" => 20])
            ],
            'firstName' => [
                tao_helpers_form_FormFactory::getValidator('NotEmpty'),
                tao_helpers_form_FormFactory::getValidator('Length', ["min" => 2, "max" => 25])
            ],
            'lastName' => [
                tao_helpers_form_FormFactory::getValidator('NotEmpty'),
                tao_helpers_form_FormFactory::getValidator('Length', ["min" => 2, "max" => 12])
            ],
            'login' => [
                tao_helpers_form_FormFactory::getValidator('NotEmpty'),
                tao_helpers_form_FormFactory::getValidator('AlphaNum'),
                tao_helpers_form_FormFactory::getValidator('Unique'),
                tao_helpers_form_FormFactory::getValidator('Length', ["min" => 2, "max" => 12])
            ],
            'mail' => [
                tao_helpers_form_FormFactory::getValidator('NotEmpty'),
                tao_helpers_form_FormFactory::getValidator('Email'),
                tao_helpers_form_FormFactory::getValidator('Length', ["min" => 6, "max" => 100])
            ],
            'password' => [
                tao_helpers_form_FormFactory::getValidator('NotEmpty'),
            ],
            'UserUIlg' => [
                tao_helpers_form_FormFactory::getValidator('Url'),
            ]
        ]);

        $report = $importer->import($class->reveal(), [
            'file' => $file,
            'map' => [
                'label'     => "0",
                'firstName' => "1",
                'lastName'  => "2",
                'login'     => "3",
                'mail'      => "4",
                'password'  => "5",
                'UserUIlg'  => "6",
            ],
        ]);

        $this->assertInstanceOf('common_report_Report', $report);
        $this->assertEquals(common_report_Report::TYPE_WARNING, $report->getType());

        $this->assertCount(6, $report->getErrors());

        //cause import has errors
        $this->assertFalse($file->exists());
    }

    /**
     * @param $path
     * @return \oat\oatbox\filesystem\File
     */
    protected function getTempFileToUpload($path)
    {
        //copy file because it should be removed
        $path = $this->getSamplePath($path);
        $file = $this->getTempDirectory()->getFile('test-import');
        $file->write(file_get_contents($path));
        $this->assertTrue($file->exists());
        return $file;
    }

    /**
     * @param $path
     * @return string
     */
    protected function getSamplePath($path)
    {
        return __DIR__ . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'samples' . DIRECTORY_SEPARATOR .
            trim($path, '\\/');
    }
}
