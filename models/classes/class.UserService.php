<?php

error_reporting(E_ALL);

/**
 * This class provide service on user management
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The Service class is an abstraction of each service instance. 
 * Used to centralize the behavior related to every servcie instances.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/models/classes/class.GenerisService.php');

/* user defined includes */
// section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001CFA-includes begin
// section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001CFA-includes end

/* user defined constants */
// section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001CFA-constants begin
// section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001CFA-constants end

/**
 * This class provide service on user management
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_UserService
    extends tao_models_classes_GenerisService
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * the core user service
     *
     * @access protected
     * @var Service
     */
    protected $generisUserService = null;

    /**
     * the list of allowed roles (ie. for login)
     *
     * @access protected
     * @var array
     */
    protected $allowedRoles = array();

    // --- OPERATIONS ---

    /**
     * constructor, call initRoles
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function __construct()
    {
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D1E begin
		
		$this->generisUserService = core_kernel_users_Service::singleton();
		$this->initRoles();
		
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D1E end
    }

    /**
     * Initialize the allowed roles.
     * To be overriden.
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initRoles()
    {
        // section 127-0-1-1-12d76932:128aaed4c91:-8000:0000000000001FA8 begin
        
    	$this->allowedRoles = array(CLASS_ROLE_TAOMANAGER);
    	
        // section 127-0-1-1-12d76932:128aaed4c91:-8000:0000000000001FA8 end
    }

    /**
     * authenticate a user
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string login
     * @param  string password
     * @return boolean
     */
    public function loginUser($login, $password)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D05 begin
		
        try{
	        foreach($this->allowedRoles as $roleUri){
	        	if($this->generisUserService->login($login, $password, $roleUri)){
	        		$returnValue = true;
	        		break;					//roles order is important, we loggin with the first found
	        	}
			}
        }
        catch(core_kernel_users_Exception $ue){
        //	print $ue->getMessage();
        }
		
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D05 end

        return (bool) $returnValue;
    }

    /**
     * initialize the current user connection:
     * connect to the API, initialize the session, etc.
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return boolean
     */
    public function connectCurrentUser()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-12d76932:128aaed4c91:-8000:0000000000001F88 begin
        
        //check if a user is in session
        if($this->generisUserService->isASessionOpened()){
        	
        	$userUri = Session::getAttribute(core_kernel_users_Service::AUTH_TOKEN_KEY);
			if(!empty($userUri)){
				
				//init the API with the login in session
				if($this->generisUserService->loginApi($userUri)){
				
					$currentUser = $this->getCurrentUser();
					
					if(!is_null($currentUser)){
						try{
							$login 			= (string)$currentUser->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_LOGIN));
	        				$password 		= (string)$currentUser->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_PASSWORD));
							
							$defaultLang 	= core_kernel_classes_DbWrapper::singleton(DATABASE_NAME)->getSetting('Deflg');
							
							$uiLang   		= $GLOBALS['default_lang'];
							$uiLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_UILG));
							if(!is_null($uiLg) && $uiLg instanceof core_kernel_classes_Resource){
								$uiLang = $uiLg->getLabel();
							}
							
							$dataLang   		= $GLOBALS['default_lang'];
							$dataLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_DEFLG));
							if(!is_null($dataLg) && $dataLg instanceof core_kernel_classes_Resource){
								$dataLang = $dataLg->getLabel();
							}
						}
						catch(common_Exception $ce){
							$defaultLang 	= $GLOBALS['default_lang'];
							$dataLang 		= $GLOBALS['default_lang'];
							$uiLang		 	= $GLOBALS['default_lang'];
						}
						
						core_kernel_classes_Session::singleton()->defaultLg = $defaultLang;
						core_kernel_classes_Session::singleton()->setLg($dataLang);
						
						if(in_array($uiLang, $GLOBALS['available_langs'])){
							Session::setAttribute('ui_lang', $uiLang);
						}
						$returnValue = true;
					}
				}
			}
        }
        
        // section 127-0-1-1-12d76932:128aaed4c91:-8000:0000000000001F88 end

        return (bool) $returnValue;
    }

    /**
     * retrieve the logged in user
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return core_kernel_classes_Resource
     */
    public function getCurrentUser()
    {
        $returnValue = null;

        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D03 begin
		
    	if($this->generisUserService->isASessionOpened()){
        	$userUri = Session::getAttribute(core_kernel_users_Service::AUTH_TOKEN_KEY);
			if(!empty($userUri)){
        		$returnValue = new core_kernel_classes_Resource($userUri);
			}
    	}
		
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D03 end

        return $returnValue;
    }

    /**
     * Check if the login is already used
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string login
     * @return boolean
     */
    public function loginExist($login)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D54 begin
		
		$returnValue = $this->generisUserService->loginExists($login);
		
        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D54 end

        return (bool) $returnValue;
    }

    /**
     * Check if the login is available (because it's unique)
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string login
     * @return boolean
     */
    public function loginAvailable($login)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D76 begin
		
		if(!empty($login)){
			$returnValue = !$this->loginExist($login);
		}
		
        // section 127-0-1-1-4660071d:12596d6b0e5:-8000:0000000000001D76 end

        return (bool) $returnValue;
    }

    /**
     * get a user by his login
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string login the user login is the unique identifier to retrieve him
     * @return core_kernel_classes_Resource
     */
    public function getOneUser($login)
    {
        $returnValue = null;

        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D4E begin
		
		if(!empty($login)){
			$user = $this->generisUserService->getOneUser($login);
			if(!is_null($user) && $user !== false){
				$userClass = $this->getClass($user);
				if(in_array($userClass->uriResource, $this->allowedRoles)){
					$returnValue = $user;
				}
				else{
					foreach($userClass->getParentClasses(true) as $parent){
						if(in_array($parent->uriResource, $this->allowedRoles)){
							$returnValue = $user;
							break;
						}
					}
				}
			}
		}
		
        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D4E end

        return $returnValue;
    }

    /**
     * Get the list of available users
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array options the user list options to order the list and paginate the results
     * @return array
     */
    public function getAllUsers($options = array())
    {
        $returnValue = array();

        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D44 begin
		
        //the users we want are instances of the role
        $users = array();
       	foreach($this->allowedRoles as $roleUri){
           $userClass = new core_kernel_classes_Class($roleUri);
           foreach($userClass->getInstances(true) as $user){
           		$users[$user->uriResource] = $user;
           }
       	}
       	
    	$keyProp = null;
       	if(isset($options['order'])){
        	switch($options['order']){
        		case 'login'		: $prop = PROPERTY_USER_LOGIN; break;
        		case 'password'		: $prop = PROPERTY_USER_PASSWORD; break;
        		case 'uilg'			: $prop = PROPERTY_USER_UILG; break;
        		case 'deflg'		: $prop = PROPERTY_USER_DEFLG; break;
        		case 'mail'			: $prop = PROPERTY_USER_MAIL; break;
        		case 'firstname'	: $prop = PROPERTY_USER_FIRTNAME; break;
        		case 'lastname'		: $prop = PROPERTY_USER_LASTNAME; break;
        		case 'name'			: $prop = PROPERTY_USER_FIRTNAME; break;
        	}
        	$keyProp = new core_kernel_classes_Property($prop);
        }
       
        $index = 0;
        foreach($users as $user){
        	$key = $index;
        	if(!is_null($keyProp)){
        		try{
        			$key = $user->getUniquePropertyValue($keyProp);
        			if(!is_null($key)){
        				if($key instanceof core_kernel_classes_Literal){
        					$returnValue[(string)$key] = $user;
        				}
        				if($key instanceof core_kernel_classes_Resource){
        					$returnValue[$key->getLabel()] = $user;
        				}
        				continue;
        			}
        		}
        		catch(common_Exception $ce){}
        	}
        	$returnValue[$key] = $user;
        	$index++;
        }
      	
    	if(isset($options['orderDir'])){
    		if(isset($options['order'])){
    			if(strtolower($options['orderDir']) == 'asc'){
   					ksort($returnValue, SORT_STRING);
    			}
    			else{
    				krsort($returnValue, SORT_STRING);
    			}
   			}
   			else{
   				if(strtolower($options['orderDir']) == 'asc'){
	   				sort($returnValue);
	   			}   
	   			else{
	   				rsort($returnValue);
	   			}  
   			}
        }
        (isset($options['start'])) 	? $start = $options['start'] 	: $start = 0;
        (isset($options['end']))	? $end	= $options['end']		: $end	= count($returnValue);
        
      //$returnValue = array_slice($returnValue, $start, $end, true);
        
        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D44 end

        return (array) $returnValue;
    }

    /**
     * Save (insert or update) the user in parameter
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource user
     * @param  array properties
     * @return boolean
     */
    public function saveUser( core_kernel_classes_Resource $user = null, $properties = array())
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D53 begin
		
		if(is_null($user)){		//insert
			if(count($this->allowedRoles) == 1){
				$user = $this->createInstance(new core_kernel_classes_Class($this->allowedRoles[0]));
				
			}
		}
		
		if(!is_null($user)){
			$returnValue = $this->bindProperties($user, $properties);
		}
		
        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D53 end

        return (bool) $returnValue;
    }

    /**
     * Remove a user
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource user
     * @return boolean
     */
    public function removeUser( core_kernel_classes_Resource $user)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D56 begin
        if(!is_null($user)){
			$returnValue = $this->generisUserService->removeUser($user);
		}
        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D56 end

        return (bool) $returnValue;
    }

} /* end of class tao_models_classes_UserService */

?>