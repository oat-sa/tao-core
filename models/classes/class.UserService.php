<?php

error_reporting(E_ALL);

/**
 * This class provide service on user management
 *
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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

					if($currentUser !== false){
						try{
							$login = (string)$currentUser->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_LOGIN));
                            $password = (string)$currentUser->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_PASSWORD));
                            $defaultLang = DEFAULT_LANG;
                            $valueProperty = new core_kernel_classes_Property(RDF_VALUE);

                            //set the user languages
							$uiLang  = DEFAULT_LANG;
							$uiLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_UILG));
							if(!is_null($uiLg) && $uiLg instanceof core_kernel_classes_Resource){
								$uiLang = $uiLg->getUniquePropertyValue($valueProperty)->literal;
							}

							$dataLang  = DEFAULT_LANG;
							$dataLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_DEFLG));
							if(!is_null($dataLg) && $dataLg instanceof core_kernel_classes_Resource){
                            	$dataLang = $dataLg->getUniquePropertyValue($valueProperty)->literal;
							}

						}
						catch(common_Exception $ce){
							$defaultLang 	= DEFAULT_LANG;
							$dataLang 	= DEFAULT_LANG;
							$uiLang	 	= DEFAULT_LANG;
						}

						core_kernel_classes_Session::singleton()->defaultLg = $defaultLang;
						core_kernel_classes_Session::singleton()->setLg($dataLang);

						if(in_array($uiLang, tao_helpers_I18n::getAvailableLangs())){
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
		$userClass = new core_kernel_classes_Class(CLASS_GENERIS_USER);
		$users = array();

		$opts = array('recursive' => 0, 'like' => false, 'additionalClasses' => $this->allowedRoles);
		if (isset($options['start'])) $opts['offset'] = $options['start'];
		if (isset($options['end'])) $opts['limit'] = $options['end'];

		$crits = array(PROPERTY_USER_LOGIN => '*');
		if (isset($options['search']) && !is_null($options['search']) && isset($options['search']['string']) && isset($ops[$options['search']['op']])) {
			$crits[$fields[$options['search']['field']]] = sprintf($ops[$options['search']['op']], $options['search']['string']);
		}
		foreach ($userClass->searchInstances($crits, $opts) as $user) {
			$users[$user->uriResource] = $user;
		}

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
     * Save (insert or update) the user in parameter
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
				$clazz = new core_kernel_classes_Class($this->allowedRoles[0]);
				$user = $this->createInstance($clazz, $this->createUniqueLabel($clazz));
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
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