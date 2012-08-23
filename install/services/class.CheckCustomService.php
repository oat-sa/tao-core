<?php
/**
 * A Service implementation aiming at calling a custom check.
 * 
 * Please refer to tao/install/api.php for more information about how to call this service.
 */
class tao_install_services_CheckCustomService extends tao_install_services_Service{
    
    /**
     * Creates a new instance of the service.
     * @param tao_install_services_Data $data The input data to be handled by the service.
     * @throws InvalidArgumentException If the input data structured is malformed or is missing data.
     */
    public function __construct(tao_install_services_Data $data){
        parent::__construct($data);
        
        $content = json_decode($this->getData()->getContent(), true);
        if (!isset($content['type']) || empty($content['type'])){
            throw new InvalidArgumentException("Missing data: 'type' must be provided.");
        }
        else if ($content['type'] !== 'CheckCustom'){
            throw new InvalidArgumentException("Unexpected type: 'type' must be equal to 'CheckCustom'.");
        }
        else if (!isset($content['value']) || empty($content['value']) || !is_array($content['value']) || count($content['value']) == 0){
            throw new InvalidArgumentException("Missing data: 'value' must be provided as a not empty array.");
        }
        else if (!isset($content['value']['name']) || empty($content['value']['name'])){
            throw new InvalidArgumentException("Missing data: 'name' must be provided.");
        }
        else if (!isset($content['value']['optional'])){
            throw new InvalidArgumentException("Missing data: 'optional' must be provided.");
        }
        else if (!isset($content['value']['extension']) || empty($content['value']['extension'])){
            throw new InvalidArgumentException("Missing data: 'extension' must be provided");
        }
        else if (isset($content['value']['parameters'])){
            // If parameters are given to the custom check...
            if (empty($content['value']['parameters']) || !is_array($content['value']['parameters']) || count($content['value']['parameters']) == 0){
                throw new InvalidArgumentException("Missing data: 'parameters' must be provided as a not empty array.");
            }
        }
    }
    
    /**
     * Executes the main logic of the service.
     * @return tao_install_services_Data The result of the service execution.
     */
    public function execute(){
        $content = json_decode($this->getData()->getContent(), true);
        $name = explode('_', $content['value']['name']);
        for ($i = 0; $i < count($name); $i++){
            $name[$i] = ucfirst($name[$i]);
        }
        $name = implode('', $name);
        
        $optional = $content['value']['optional'];
        $extension = $content['value']['extension'];
        
        $checkClassName = "${extension}_install2_checks_${name}";
        
        try{
            $checkClass = new ReflectionClass($checkClassName);
            $check = $checkClass->newInstanceArgs(array("custom_${extension}_${name}", $optional));
            $report = $check->check();
            
            $data = array('type' => 'CheckCustomReport',
                          'value' => array('status' => $report->getStatusAsString(),
                                           'message' => $report->getMessage(),
                                           'name' => $name,
                                           'extension' => $extension,
                                           'optional' => $optional));
                                           
            $this->setResult(new tao_install_services_Data(json_encode($data)));    
        }
        catch (LogicException $e){
            throw new tao_install_services_UnknownCustomCheckException($name, $extension);
        }
    }
}
?>