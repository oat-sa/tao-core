<?php

/**
 * A Service implementation aiming at checking the version of the PHP Runtime currently running
 * on the host system.
 * 
 * Please refer to tao/install/api.php for more information about how to call this service.
 */
class tao_install_services_CheckPHPRuntimeService 
	extends tao_install_services_Service
	implements tao_install_services_CheckService
{
    
    /**
     * Creates a new instance of the service.
     * @param tao_install_services_Data $data The input data to be handled by the service.
     * @throws InvalidArgumentException If the input data structured is malformed or is missing data.
     */
    public function __construct($data){
        parent::__construct($data);
    }
    
    /**
     * Executes the main logic of the service.
     * @return tao_install_services_Data The result of the service execution.
     */
    public function execute(){

        $runtime = self::buildComponent($this->getData());
        $report = $runtime->check();
        
        $this->setResult(self::buildResult($this->getData(), $report, $runtime));
    }
    
    public static function checkData(tao_install_services_Data $data){
    	$content = json_decode($data->getContent(), true);
        if (!isset($content['type']) || empty($content['type']) || $content['type'] != 'CheckPHPRuntime'){
            throw new InvalidArgumentException("Unexpected type: 'type' must be equal to 'CheckPHPRuntime'.");
        }
        else if (!isset($content['value']) || empty($content['value'])){
            throw new InvalidArgumentException("Missing data: 'value' must be provided.");
        }
        else if (!isset($content['value']['id']) || empty($content['value']['id'])){
        	throw new InvalidArgumentException("Missing data: 'id' must be provided.");
        }
        else if (!isset($content['value']['min'])){
            throw new InvalidArgumentException("Missing data: 'min' must be provided.");
        }
    }
    
    public static function buildComponent(tao_install_services_Data $data){
    	$content = json_decode($data->getContent(), true);
        $min = $content['value']['min'];
        $max = (isset($content['value']['max'])) ? $content['value']['max'] : null;
        
        if (isset($content['value']['optional'])){
        	$optional = $content['value']['optional'];
        }
        else{
        	$optional = false;
        }
        
        return common_configuration_ComponentFactory::buildPHPRuntime($min, $max, $optional);
    }
    
    public static function buildResult(tao_install_services_Data $data,
									   common_configuration_Report $report,
									   common_configuration_Component $component){
	
		$content = json_decode($data->getContent(), true);
        $id = $content['value']['id'];
        
        $value = array('status' => $report->getStatusAsString(),
        			   'id' => $id,
                       'message' => $report->getMessage(),
                       'min' => $component->getMin(),
                       'value' => $component->getValue(),
        			   'optional' => $component->isOptional());

        $max = $component->getMax();
        if (!empty($max)){
            $value['max'] = $component->getMax();
        }
                       
                       
        $data = array('type' => 'PHPRuntimeReport',
                      'value' => $value);
        
        return new tao_install_services_Data(json_encode($data));
	}
}
?>