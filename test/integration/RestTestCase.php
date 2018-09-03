<?php
namespace oat\tao\test\integration;

require_once dirname(__FILE__) . '/../../includes/raw_start.php';

abstract class RestTestCase extends RestTestRunner
{

    public abstract function serviceProvider();

    /**
     * @dataProvider serviceProvider
     */
    public function testHttp($service)
    {
        $url = $this->host . $service;
        // HTTP Basic
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            "Accept: application/json"
        ));
        
        // should return a 401
        curl_setopt($process, CURLOPT_USERPWD, "dummy:dummy");
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        
        $data = curl_exec($process);
        $http_status = curl_getinfo($process, CURLINFO_HTTP_CODE);
        
        $this->assertEquals("401", $http_status, 'bad response on url ' . $url . ' return ' . $http_status);
        curl_close($process);
        
        // should return a 401
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            "Accept: application/json"
        ));
        curl_setopt($process, CURLOPT_USERPWD, $this->login . ":dummy");
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($process);
        $http_status = curl_getinfo($process, CURLINFO_HTTP_CODE);
        $this->assertEquals($http_status, "401");
        curl_close($process);
        
        // should return a 406
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            "Accept: dummy/dummy"
        ));
        curl_setopt($process, CURLOPT_USERPWD, $this->login . ":" . $this->password);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($process);
        $http_status = curl_getinfo($process, CURLINFO_HTTP_CODE);
        $this->assertEquals($http_status, "406");
        curl_close($process);
        
        // should return a 200
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            "Accept: application/xml"
        ));
        curl_setopt($process, CURLOPT_USERPWD, $this->login . ":" . $this->password);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($process);
        $http_status = curl_getinfo($process, CURLINFO_HTTP_CODE);
        $this->assertEquals($http_status, "200");
        
        // should return a 200, should return content encoding application/xml
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8"
        ));
        curl_setopt($process, CURLOPT_USERPWD, $this->login . ":" . $this->password);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($process);
        $http_status = curl_getinfo($process, CURLINFO_HTTP_CODE);
        $this->assertEquals($http_status, "200");
        $contentType = curl_getinfo($process, CURLINFO_CONTENT_TYPE);
        $this->assertEquals($contentType, "application/xml");
        curl_close($process);
        
        // should return a 200
        $http_status = $this->curl($url, CURLOPT_HTTPGET, CURLINFO_HTTP_CODE);
                        $this->assertEquals($http_status, "200");
    
    }
    
    /**
     * @dataProvider serviceProvider
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetAll($service, $topclass = null){
        if($topclass == null){
            $this->markTestSkipped('This test do not apply to topclass' , $topclass);
        }
        $url = $this->host.$service;
        $returnedData = $this->curl($url);
        $data = json_decode($returnedData, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue( $data["success"]);
    
        $ItemClass = new \core_kernel_classes_Class($topclass);
        $instances = $ItemClass->getInstances(true);
        foreach ($data['data'] as $results){
            $this->assertInternalType('array', $results);
            $this->assertArrayHasKey('uri', $results);
            $this->assertArrayHasKey('properties', $results);
            $this->assertInternalType('array', $instances);
    
            $this->assertArrayHasKey($results['uri'], $instances);
            $resource = $instances[$results['uri']];
    
            foreach ($results['properties'] as $propArray){
                $this->assertInternalType('array', $propArray);
    
                $this->assertArrayHasKey('predicateUri',$propArray);
                $prop = new \core_kernel_classes_Property($propArray['predicateUri']);
                $values = $resource->getPropertyValues($prop);
                $this->assertArrayHasKey('values',$propArray);
                $current = current($propArray['values']);
                $this->assertInternalType('array',$current);
    
                $this->assertArrayHasKey('valueType',$current);
                if (\common_Utils::isUri(current($values))){
                    $this->assertEquals('resource', $current['valueType']);
    
                } else {
                    $this->assertEquals('literal', $current['valueType']);
                }
                $this->assertArrayHasKey('value',$current);
                $this->assertEquals(current($values), $current['value']);
    
            }
             
        }
         
    }

}
