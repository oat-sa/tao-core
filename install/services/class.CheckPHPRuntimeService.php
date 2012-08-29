<?php

/**
 * A Service implementation aiming at checking the version of the PHP Runtime currently running
 * on the host system.
 * 
 * Please refer to tao/install/api.php for more information about how to call this service.
 */
class tao_install_services_CheckPHPRuntimeService extends tao_install_services_Service{
    
    /**
     * Creates a new instance of the service.
     * @param tao_install_services_Data $data The input data to be handled by the service.
     * @throws InvalidArgumentException If the input data structured is malformed or is missing data.
     */
    public function __construct($data){
        parent::__construct($data);
        
        $content = json_decode($this->getData()->getContent(), true);
        if (!isset($content['type']) || empty($content['type']) || $content['type'] != 'CheckPHPRuntime'){
            throw new InvalidArgumentException("Unexpected type: 'type' must be equal to 'CheckPHPRuntime'.");
        }
        else if (!isset($content['value']) || empty($content['value'])){
            throw new InvalidArgumentException("Missing data: 'value' must be provided.");
        }
        else if (!isset($content['value']['min'])){
            throw new InvalidArgumentException("Missing data: 'min' must be provided.");
        }
        else if (!isset($content['value']['optional'])){
            throw new InvalidArgumentException("Missing data: 'optional' must be provided.");
        }
    }
    
    /**
     * Executes the main logic of the service.
     * @return tao_install_services_Data The result of the service execution.
     */
    public function execute(){
        $content = json_decode($this->getData()->getContent(), true);
        $min = $content['value']['min'];
        $max = (isset($content['value']['max'])) ? $content['value']['max'] : null;
        $optional = ($content['value']['optional'] == 'true') ? true : false;
        
        // Handle the name of this check.
        $name = 'php_version';
        if (isset($content['value']['name']) && !empty($content['value']['name'])){
            $name = $content['value']['name'];
        }
        
        $runtime = new common_configuration_PHPRuntime($min, $max, "PHP Runtime", $optional);
        $report = $runtime->check();
        
        $value = array('status' => $report->getStatusAsString(),
                       'message' => $report->getMessage(),
                       'min' => $min,
                       'value' => $runtime->getValue(),
                       'name' => $name,
        			   'optional' => $optional);
                       
        if (!empty($max)){
            $value['max'] = $max;
        }
                       
                       
        $data = array('type' => 'PHPRuntimeReport',
                      'value' => $value);
                      
        $this->setResult(new tao_install_services_Data(json_encode($data)));
    }
}
?>