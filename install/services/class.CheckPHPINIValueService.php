<?php

/**
 * A Service implementation aiming at checking the existence and the validity of a PHP
 * INI value on the host system.
 * 
 * Please refer to tao/install/api.php for more information about how to call this service.
 */
class tao_install_services_CheckPHPINIValueService 
	extends tao_install_services_Service
	implements tao_install_services_CheckService
 {
    
    /**
     * Creates a new instance of the service.
     * @param tao_install_services_Data $data The input data to be handled by the service.
     * @throws InvalidArgumentException If the input data structured is malformed or is missing data.
     */
    public function __construct(tao_install_services_Data $data){
        parent::__construct($data);
    }
    
    /**
     * Executes the main logic of the service.
     * @return tao_install_services_Data The result of the service execution.
     */
    public function execute(){
    	
        $ini = self::buildComponent($this->getData());
        $report = $ini->check();                          
        $this->setResult(self::buildResult($this->getData(), $report, $ini));
    }
    
    public static function checkData(tao_install_services_Data $data){
    	// Check input data.
        $content = json_decode($data->getContent(), true);
        if (!isset($content['type']) || empty($content['type']) || $content['type'] !== 'CheckPHPINIValue'){
            throw new InvalidArgumentException("Unexpected type: 'type' must be equal to 'CheckPHPINIValue'.");
        }
        else if (!isset($content['value']) || empty($content['value'])){
            throw new InvalidArgumentException("Missing data: 'value' must be provided.");
        }
        if (!isset($content['value']['id']) || empty($content['value']['id'])){
        	throw new InvalidArgumentException("Missing data: 'id' must be provided.");
        }
        if (!isset($content['value']['name'])){
            throw new InvalidArgumentException("Missing data: 'name' must be provided.");
        }
        else if (!isset($content['value']['value'])){
            throw new InvalidArgumentException("Missing data: 'value' must be provided.");
        }
    }
    
    public static function buildComponent(tao_install_services_Data $data){
    	$content = json_decode($data->getContent(), true);
        $value = $content['value']['value'];
        $name = $content['value']['name'];
    	if (isset($content['value']['optional'])){
        	$optional = $content['value']['optional'];
        }
        else{
        	$optional = false;
        }
        
        return common_configuration_ComponentFactory::buildPHPINIValue($name, $value, $optional);
    }
    
    public static function buildResult(tao_install_services_Data $data,
									   common_configuration_Report $report,
									   common_configuration_Component $component){
									   	
		$content = json_decode($data->getContent(), true);
        $value = $content['value']['value'];
        $id = $content['value']['id'];
        
        $data = array('type' => 'PHPINIValueReport',
                      'value' => array('status' => $report->getStatusAsString(),
        							   'id' => $id,
                                       'message' => $report->getMessage(),
                                       'expectedValue' => $value,
                                       'value' => $component->getValue(),
                                       'optional' => $component->isOptional(),
        							   'name' => $component->getName()));
        
        return new tao_install_services_Data(json_encode($data));
	}
}
?>