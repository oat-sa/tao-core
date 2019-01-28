<?php

namespace oat\tao\test\integration;

use common_Utils;
use core_kernel_classes_Class;
use core_kernel_classes_Property;

abstract class RestTestCase extends RestTestRunner
{
    abstract public function serviceProvider();

    public function setUp()
    {
        parent::setUp();

        require_once __DIR__ . '/../../includes/raw_start.php';
    }

    /**
     * @dataProvider serviceProvider
     */
    public function testHttp($service)
    {
        $url = $this->host . $service;
        // HTTP Basic
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
        ]);

        // should return a 401
        curl_setopt($process, CURLOPT_USERPWD, 'dummy:dummy');
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, true);

        curl_exec($process);
        $httpStatus = curl_getinfo($process, CURLINFO_HTTP_CODE);

        $this->assertEquals('401', $httpStatus, 'bad response on url ' . $url . ' return ' . $httpStatus);
        curl_close($process);

        // should return a 401
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
        ]);
        curl_setopt($process, CURLOPT_USERPWD, $this->login . ':dummy');
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, true);

        curl_exec($process);
        $httpStatus = curl_getinfo($process, CURLINFO_HTTP_CODE);
        $this->assertEquals($httpStatus, '401');
        curl_close($process);

        // should return a 406
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, [
            'Accept: dummy/dummy',
        ]);
        curl_setopt($process, CURLOPT_USERPWD, $this->login . ':' . $this->password);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, true);

        curl_exec($process);
        $httpStatus = curl_getinfo($process, CURLINFO_HTTP_CODE);
        $this->assertEquals($httpStatus, '406');
        curl_close($process);

        // should return a 200
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, [
            'Accept: application/xml',
        ]);
        curl_setopt($process, CURLOPT_USERPWD, $this->login . ':' . $this->password);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, true);

        curl_exec($process);
        $httpStatus = curl_getinfo($process, CURLINFO_HTTP_CODE);
        $this->assertEquals($httpStatus, '200');

        // should return a 200, should return content encoding application/xml
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, [
            'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ]);
        curl_setopt($process, CURLOPT_USERPWD, $this->login . ':' . $this->password);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, true);

        curl_exec($process);
        $httpStatus = curl_getinfo($process, CURLINFO_HTTP_CODE);
        $this->assertEquals($httpStatus, '200');
        $contentType = curl_getinfo($process, CURLINFO_CONTENT_TYPE);
        $this->assertEquals($contentType, 'application/xml');
        curl_close($process);

        // should return a 200
        $httpStatus = $this->curl($url, CURLOPT_HTTPGET, CURLINFO_HTTP_CODE);
        $this->assertEquals($httpStatus, '200');
    }

    /**
     * @dataProvider serviceProvider
     * @author       Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetAllResources($service, $topClass = null)
    {
        if ($topClass === null) {
            $this->markTestSkipped(sprintf("This test do not apply to topclass: '%s'", $topClass));
        }
        $url = $this->host . $service;
        $returnedData = $this->curl($url);
        $data = json_decode($returnedData, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        $itemClass = new core_kernel_classes_Class($topClass);
        $instances = $itemClass->getInstances(true);
        foreach ($data['data'] as $results) {
            $this->assertInternalType('array', $results);
            $this->assertArrayHasKey('uri', $results);
            $this->assertArrayHasKey('properties', $results);
            $this->assertInternalType('array', $instances);

            $this->assertArrayHasKey($results['uri'], $instances);
            $resource = $instances[$results['uri']];

            foreach ($results['properties'] as $propArray) {
                $this->assertInternalType('array', $propArray);

                $this->assertArrayHasKey('predicateUri', $propArray);
                $prop = new core_kernel_classes_Property($propArray['predicateUri']);
                $values = $resource->getPropertyValues($prop);
                $this->assertArrayHasKey('values', $propArray);
                $current = current($propArray['values']);
                $this->assertInternalType('array', $current);

                $this->assertArrayHasKey('valueType', $current);
                if (common_Utils::isUri(current($values))) {
                    $this->assertEquals('resource', $current['valueType']);
                } else {
                    $this->assertEquals('literal', $current['valueType']);
                }
                $this->assertArrayHasKey('value', $current);
                $this->assertEquals(current($values), $current['value']);
            }
        }
    }

    /**
     * @dataProvider serviceProvider
     */
    public function testGetOneResource($service, $topClass = null)
    {
        if ($topClass === null) {
            $this->markTestSkipped(sprintf("This test do not apply to topclass: '%s'", $topClass));
        }

        $rdfResource = $this->getRandomRdfInstance($topClass);

        $url = $this->host . $service . '?uri=' . urlencode($rdfResource->getUri());

        $returnedData = $this->curl($url);
        $data = json_decode($returnedData, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        $itemClass = new core_kernel_classes_Class($topClass);
        $instance = $itemClass->getResource(urlencode($rdfResource->getUri()));

        $resourceData = $data['data'];

        $this->assertEquals(urldecode($instance->getUri()), $resourceData['uri']);
    }

    /**
     * @dataProvider serviceProvider
     */
    public function testGetDefaultRootClassSubClasses($service, $topClass = null)
    {
        if ($topClass === null) {
            $this->markTestSkipped(sprintf("This test do not apply to topclass: '%s'", $topClass));
        }

        $itemClass = new core_kernel_classes_Class($topClass);

        $url = $this->host . $service . '/classes?filter=subClasses';

        $returnedData = $this->curl($url);
        $data = json_decode($returnedData, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        $resourceData = $data['data'];

        $i = 0;

        foreach ($itemClass->getSubClasses() as $subClass) {
            $this->assertEquals($subClass->getUri(), $resourceData[$i]['uri']);

            $i++;
        }
    }

    /**
     * @dataProvider serviceProvider
     */
    public function testGetDefaultRootClassInstances($service, $topClass = null)
    {
        if ($topClass === null) {
            $this->markTestSkipped(sprintf("This test do not apply to topclass: '%s'", $topClass));
        }

        $itemClass = new core_kernel_classes_Class($topClass);

        $url = $this->host . $service . '/classes?filter=instances';

        $returnedData = $this->curl($url);
        $data = json_decode($returnedData, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        $resourceData = $data['data'];

        $i = 0;

        foreach ($itemClass->getInstances() as $instance) {
            $this->assertEquals($instance->getUri(), $resourceData[$i]['uri']);

            $i++;
        }
    }

    /**
     * @dataProvider serviceProvider
     */
    public function testGetSpecificClassSubClasses($service, $topClass = null)
    {
        if ($topClass === null) {
            $this->markTestSkipped(sprintf("This test do not apply to topclass: '%s'", $topClass));
        }

        $itemClass = new core_kernel_classes_Class($topClass);

        $url = $this->host . $service . '/classes?filter=subClasses&uri=' . urlencode($itemClass->getUri());

        $returnedData = $this->curl($url);
        $data = json_decode($returnedData, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        $resourceData = $data['data'];

        $i = 0;

        foreach ($itemClass->getSubClasses() as $subClass) {
            $this->assertEquals($subClass->getUri(), $resourceData[$i]['uri']);

            $i++;
        }
    }

    /**
     * @dataProvider serviceProvider
     */
    public function testGetSpecificClassInstances($service, $topClass = null)
    {
        if ($topClass === null) {
            $this->markTestSkipped(sprintf("This test do not apply to topclass: '%s'", $topClass));
        }

        $itemClass = new core_kernel_classes_Class($topClass);

        $url = $this->host . $service . '/classes?filter=instances&uri=' . urlencode($itemClass->getUri());

        $returnedData = $this->curl($url);
        $data = json_decode($returnedData, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        $resourceData = $data['data'];

        $i = 0;

        foreach ($itemClass->getInstances() as $instance) {
            $this->assertEquals($instance->getUri(), $resourceData[$i]['uri']);

            $i++;
        }
    }

    private function getRandomRdfInstance($topClass)
    {
        $instances = (new core_kernel_classes_Class($topClass))->getInstances();

        return $instances[array_rand($instances)];
    }
}
