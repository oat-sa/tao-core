<?php

/**
 * A Service implementation aiming at checking a series of configurable things
 * such as PHP Extensions, PHP INI Values, PHP Runtime, File system,...
 * 
 * Please refer to tao/install/api.php for more information about how to call this service.
 */
class tao_install_services_CheckPHPConfigService extends tao_install_services_Service{
    
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
        else if ($content['type'] !== 'CheckPHPConfig'){
            throw new InvalidArgumentException("Unexpected type: 'type' must be equal to 'CheckPHPConfig'.");
        }
        else if (!isset($content['value']) || empty($content['value']) || count($content['value']) == 0){
            throw new InvalidArgumentException("Missing data: 'value' must be provided as a not empty array.");
        }
        else{
            $acceptedTypes = array('CheckPHPExtension', 'CheckPHPINIValue', 'CheckPHPRuntime', 'CheckPHPDatabaseDriver', 'CheckFileSystemComponent', 'CheckCustom');
            
            foreach ($content['value'] as $config){
                if (!isset($config['type']) || empty($config['type']) || !in_array($config['type'], $acceptedTypes)){
                    throw new InvalidArgumentException("Missing data: configuration 'type' must provided.");
                }
            }
        }
    }
    
    /**
     * Executes the main logic of the service.
     * @return tao_install_services_Data The result of the service execution.
     */
    public function execute(){
        $content = json_decode($this->getData()->getContent(), true);
        $resultData = json_encode(array('type' => 'ReportCollection',
                                        'value' => '{RETURN_VALUE}'));
        $resultValue = array();
        
        foreach ($content['value'] as $config){
            $class = new ReflectionClass('tao_install_services_' . $config['type'] . 'Service');
            $data = new tao_install_services_Data(json_encode($config));
            $service = $class->newInstance($data);
            $service->execute();
            $resultValue[] = $service->getResult()->getContent();
        }
        
        $resultData = str_replace('"{RETURN_VALUE}"', '[' . implode(',', $resultValue) . ']', $resultData);
        $this->setResult(new tao_install_services_Data($resultData));
    }
}
?>