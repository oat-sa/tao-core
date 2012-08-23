<?php

/**
 * A Service implementation aiming at checking the existence and the validity of a PHP
 * INI value on the host system.
 * 
 * Please refer to tao/install/api.php for more information about how to call this service.
 */
class tao_install_services_CheckPHPINIValueService extends tao_install_services_Service {
    
    /**
     * Creates a new instance of the service.
     * @param tao_install_services_Data $data The input data to be handled by the service.
     * @throws InvalidArgumentException If the input data structured is malformed or is missing data.
     */
    public function __construct(tao_install_services_Data $data){
        parent::__construct($data);
        
        // Check input data.
        $content = json_decode($this->getData()->getContent(), true);
        if (!isset($content['type']) || empty($content['type']) || $content['type'] !== 'CheckPHPINIValue'){
            throw new InvalidArgumentException("Unexpected type: 'type' must be equal to 'CheckPHPINIValue'.");
        }
        else if (!isset($content['value']) || empty($content['value'])){
            throw new InvalidArgumentException("Missing data: 'value' must be provided.");
        }
        if (!isset($content['value']['name'])){
            throw new InvalidArgumentException("Missing data: 'name' must be provided.");
        }
        else if (!isset($content['value']['value'])){
            throw new InvalidArgumentException("Missing data: 'value' must be provided.");
        }
        else if (!isset($content['value']['optional'])){
            throw new InvalidArgumentException("Missing data: 'optional' must be provided");
        }
    }
    
    /**
     * Executes the main logic of the service.
     * @return tao_install_services_Data The result of the service execution.
     */
    public function execute(){
        $content = json_decode($this->getData()->getContent(), true);
        $value = $content['value']['value'];
        $name = $content['value']['name'];
        $optional = ($content['value']['optional'] == 'true') ? true : false;
        
        $ini = new common_configuration_PHPINIValue($value, $name, $optional);
        $report = $ini->check();
        
        $data = array('type' => 'PHPINIValueReport',
                      'value' => array('status' => $report->getStatusAsString(),
                                       'message' => $report->getMessage(),
                                       'expectedValue' => $value,
                                       'value' => $ini->getValue(),
                                       'name' => $ini->getName(),
                                       'optional' => $optional));
                                       
        $this->setResult(new tao_install_services_Data(json_encode($data)));
    }
}
?>