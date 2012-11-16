<?php

/**
 * A Service implementation aiming at checking the existence and the availability of
 * a target PHP Extension on the host system.
 * 
 * Please refer to tao/install/api.php for more information about how to call this service.
 */
class tao_install_services_CheckPHPExtensionService extends tao_install_services_Service{
    
    /**
     * Creates a new instance of the service.
     * @param tao_install_services_Data $data The input data to be handled by the service.
     * @throws InvalidArgumentException If the input data structured is malformed or is missing data.
     */
    public function __construct(tao_install_services_Data $data){
        parent::__construct($data);
        
        // Check data integrity.
        $content = json_decode($this->getData()->getContent(), true);
        if (!isset($content['type']) || empty($content['type']) || $content['type'] !== 'CheckPHPExtension'){
            throw new InvalidArgumentException("Unexpected type: 'type' must be equal to 'CheckPHPExtension'.");
        }
        else if (!isset($content['value']) || empty($content['value'])){
            throw new InvalidArgumentException("Missing data: 'value' must be provided.");
        }
        else if (!isset($content['value']['id']) || empty($content['value']['id'])){
        	throw new InvalidArgumentException("Missing data: 'id' must be provided");
        }
        else if (!isset($content['value']['name'])){
            throw new InvalidArgumentException("Missing data: 'name' must be provided.");
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
        $extensionName = $content['value']['name'];
        $optional = ($content['value']['optional'] == 'true') ? true : false;
        $ext = new common_configuration_PHPExtension(null, null, $extensionName);
        $report = $ext->check();
        $id = $content['value']['id'];
        
        $data = array('type' => 'PHPExtensionReport',
                      'value' => array('status' => $report->getStatusAsString(),
                                       'message' => $report->getMessage(),
                                       'optional' => $optional,
                                       'name' => $extensionName,
        							   'id' => $id));
                                       
        $this->setResult(new tao_install_services_Data(json_encode($data)));
    }
}
?>