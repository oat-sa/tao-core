<?php
/**
 * A Service implementation aiming at checking the existence and the validity of rights
 * of file system components, in other words files and directorties.
 * 
 * Please refer to tao/install/api.php for more information about how to call this service.
 */
class tao_install_services_CheckFileSystemComponentService extends tao_install_services_Service{
    
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
        else if ($content['type'] !== 'CheckFileSystemComponent'){
            throw new InvalidArgumentException("Unexpected type: 'type' must be equal to 'CheckFileSystemComponent'.");
        }
        else if (!isset($content['value']) || empty($content['value']) || count($content['value']) == 0){
            throw new InvalidArgumentException("Missing data: 'value' must be provided as a not empty array.");
        }
        else if (!isset($content['value']['id']) || empty($content['value']['id'])){
        	throw new InvalidArgumentException("Missing data: 'id' must be provided.");
        }
        else if (!isset($content['value']['rights']) || empty($content['value']['rights'])){
            throw new InvalidArgumentException("Missing data: 'rights' must be provided.");
        }
        else if (!isset($content['value']['location']) || empty($content['value']['location'])){
            throw new InvalidArgumentException("Missing data: 'location' must be provided.");
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
        $location = $content['value']['location'];
        $rights = $content['value']['rights'];
        $optional = ($content['value']['optional'] == 'true') ? true : false;
        $id = $content['value']['id'];
        $root = dirname(__FILE__) . '/../../../';
        $fsc = new common_configuration_FileSystemComponent($root . $location, $rights, $optional);
        $report = $fsc->check();
        
        $data = array('type' => 'FileSystemComponentReport',
                      'value' => array('status' => $report->getStatusAsString(),
                                       'message' => $report->getMessage(),
        							   'id' => $id,
                                       'optional' => $fsc->isOptional(),
                                       'isReadable' => $fsc->isReadable(),
                                       'isWritable' => $fsc->isWritable(),
                                       'isExecutable' => $fsc->isExecutable(),
        							   'expectedRights' => $rights,
        							   'isFile' => is_file($root . $location),
        							   'location' => $location));
                                       
        $this->setResult(new tao_install_services_Data(json_encode($data)));
    }
}
?>