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
    }
    
    /**
     * Executes the main logic of the service.
     * @return tao_install_services_Data The result of the service execution.
     */
    public function execute(){
		// contains an array of 'component', associated input 'data' 
		// and service 'class'.
    	$componentToData = array(); 
    	
        $content = json_decode($this->getData()->getContent(), true);
        $resultData = json_encode(array('type' => 'ReportCollection',
                                        'value' => '{RETURN_VALUE}'));
        
        // Deal with checks to be done.
        $collection = new common_configuration_ComponentCollection();
        foreach ($content['value'] as $config){
        	$class = new ReflectionClass('tao_install_services_' . $config['type'] . 'Service');
        	$buildMethod = $class->getMethod('buildComponent');
        	$args = new tao_install_services_Data(json_encode($config));
        	$component = $buildMethod->invoke(null, $args);
        	$collection->addComponent($component);
        	$componentToData[] = array('component' => $component, 'data' => $args, 'class' => $class);
        }
        
        
        // Deal with results to be sent to the client.
        $resultValue = array();
        $reports = $collection->check();
        foreach($reports as $r){
        	$component = $r->getComponent();
        	
        	
        	// For the retrieved component, what was the associated data and class ?
        	$associatedData = null;
        	$class = null;
        	foreach ($componentToData as $ctd)
        	{
        		if ($component == $ctd['component']){
        			$associatedData = $ctd['data'];
        			$class = $ctd['class'];
        		}
        	}
        	
        	$buildMethod = $class->getMethod('buildResult');
        	$serviceResult = $buildMethod->invoke(null, $associatedData, $r, $component);
        	$resultValue[] = $serviceResult->getContent();
        }
        
        // Sort by 'optional'.
        usort($resultValue, array('tao_install_services_CheckPHPConfigService' , 'sortReports'));
        
        
        $resultData = str_replace('"{RETURN_VALUE}"', '[' . implode(',', $resultValue) . ']', $resultData);
        $this->setResult(new tao_install_services_Data($resultData));
    }
    
    /**
     * Report sorting function.
     * @param string $a JSON encoded report.
     * @param string $b JSON encoded report.
     * @return boolean Comparison result.
     */
    private static function sortReports ($a, $b){
    	$a = json_decode($a, true);
    	$b = json_decode($b, true);
    	
    	if ($a['value']['optional'] == $b['value']['optional']){
    		return 0;
    	}
    	else{
    		return ($a['value']['optional'] < $b['value']['optional']) ? -1 : 1;
    	}
    }
    
    public static function checkData(tao_install_services_Data $data){
    	$content = json_decode($data->getContent(), true);
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
                else{
                	$className = 'tao_install_services_' . $config['type'] . 'Service';
                	$data = new tao_install_services_Data(json_encode($config));
                	call_user_func($className . '::checkData', $data);
                }
            }
        }
    }
}
?>