<?php

error_reporting(E_ALL);

/**
 * This class provide service on user management
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
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
 * @author Joel Bout, <joel.bout@tudor.lu>
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
 * @author Joel Bout, <joel.bout@tudor.lu>
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
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function __construct()
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
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function initRoles()
    {
        // section 127-0-1-1-12d76932:128aaed4c91:-8000:0000000000001FA8 begin

    	// expects a subclass of class_role, not an instance
    	$this->allowedRoles = array(CLASS_ROLE_BACKOFFICE);

        // section 127-0-1-1-12d76932:128aaed4c91:-8000:0000000000001FA8 end
    }

    /**
     * authenticate a user
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
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
	        		
	        		common_Logger::i('User '.$login.' logged in', array('TAO'));
	        		
	        		// init languages
        			$currentUser = $this->getCurrentUser();
        			$valueProperty = new core_kernel_classes_Property(RDF_VALUE);
        			
        			$uiLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_UILG));
        			if(!is_null($uiLg) && $uiLg instanceof core_kernel_classes_Resource) {
        				$code = $uiLg->getUniquePropertyValue($valueProperty);
						core_kernel_classes_Session::singleton()->setInterfaceLanguage($code);
        			}

					$dataLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_DEFLG));
					if(!is_null($dataLg) && $dataLg instanceof core_kernel_classes_Resource){
        				$code = $dataLg->getUniquePropertyValue($valueProperty);
						core_kernel_classes_Session::singleton()->setDataLanguage($code);
					}
					
	        		$returnValue = true;
	        		break;					//roles order is important, we loggin with the first found
	        	} else {
	        		common_Logger::w('User '.$login.' login failed', array('TAO'));
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
     * retrieve the logged in user
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return core_kernel_classes_Resource
     */
    public function getCurrentUser()
    {
        $returnValue = null;

        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D03 begin
    	if($this->generisUserService->isASessionOpened()){
        	$userUri = core_kernel_classes_Session::singleton()->getUserUri();
			if(!empty($userUri)){
        		$returnValue = new core_kernel_classes_Resource($userUri);
			} else {
				common_Logger::d('no userUri');
			}
    	}
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D03 end

        return $returnValue;
    }

    /**
     * Check if the login is already used
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
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
     * @author Joel Bout, <joel.bout@tudor.lu>
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
     * @author Joel Bout, <joel.bout@tudor.lu>
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
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  array options the user list options to order the list and paginate the results
     * @return array
     */
    public function getAllUsers($options = array())
    {
        $returnValue = array();

        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D44 begin

        //the users we want are instances of the role
		$fields = array('login' => PROPERTY_USER_LOGIN,
						'password' => PROPERTY_USER_PASSWORD,
						'uilg' => PROPERTY_USER_UILG,
						'deflg' => PROPERTY_USER_DEFLG,
						'mail' => PROPERTY_USER_MAIL,
						'firstname' => PROPERTY_USER_FIRTNAME,
						'lastname' => PROPERTY_USER_LASTNAME,
						'name' => PROPERTY_USER_FIRTNAME);
		$ops = array('eq' => "%s",
					 'bw' => "%s*",
					 'ew' => "*%s",
					 'cn' => "*%s*");
		
		$rolesClass = new core_kernel_classes_Class(CLASS_ROLE);
		$users = array();

		$opts = array('recursive' => true, 'like' => false);
		if (isset($options['start'])) $opts['offset'] = $options['start'];
		if (isset($options['end'])) $opts['limit'] = $options['end'];
		if (isset($options['filteredRoles'])) $opts['additionalClasses'] = $options['filteredRoles'];
		
		
		$crits = array(PROPERTY_USER_LOGIN => '*');
		if (isset($options['search']) && !is_null($options['search']) && isset($options['search']['string']) && isset($ops[$options['search']['op']])) {
			$crits[$fields[$options['search']['field']]] = sprintf($ops[$options['search']['op']], $options['search']['string']);
		}
		
		$users = $rolesClass->searchInstances($crits, $opts);
				
		
    	$keyProp = null;
       	if (isset($options['order'])) {
        	$keyProp = new core_kernel_classes_Property($fields[$options['order']]);
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
     * Remove a user
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
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

    /**
     * Short description of method addAllowedRole
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string role
     * @return mixed
     */
    public function addAllowedRole($role)
    {
        // section 127-0-1-1-6426161e:12f0225bbdf:-8000:0000000000002D40 begin

    	$this->allowedRoles[] = $role;

        // section 127-0-1-1-6426161e:12f0225bbdf:-8000:0000000000002D40 end
    }

    /**
     * Short description of method getAllowedRoles
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function getAllowedRoles()
    {
        $returnValue = array();

        // section 127-0-1-1--2224001b:1341c506b75:-8000:0000000000004424 begin
		$returnValue = $this->allowedRoles;
        // section 127-0-1-1--2224001b:1341c506b75:-8000:0000000000004424 end

        return (array) $returnValue;
    }

    /**
     * Short description of method logout
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return boolean
     */
    public function logout()
    {
        $returnValue = (bool) false;

        // section 10-13-1-85-4bfc518d:13586bdbc87:-8000:00000000000037E7 begin
        $returnValue = $this->generisUserService->logout();
        // section 10-13-1-85-4bfc518d:13586bdbc87:-8000:00000000000037E7 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getCurrentUserRoles
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function getCurrentUserRoles()
    {
        $returnValue = array();

        // section 127-0-1-1--118c10aa:136066bff8b:-8000:000000000000386A begin
		$user = $this->getCurrentUser();
		if (!is_null($user)) {
			$returnValue = $user->getPropertyValues(new core_kernel_classes_Property(RDF_TYPE));
		}
        // section 127-0-1-1--118c10aa:136066bff8b:-8000:000000000000386A end

        return (array) $returnValue;
    }

} /* end of class tao_models_classes_UserService */

?>