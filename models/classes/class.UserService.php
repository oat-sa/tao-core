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
require_once('tao/models/classes/class.Service.php');

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
    extends tao_models_classes_Service
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * the database wrapper instance
     *
     * @access protected
     * @var DbWrapper
     */
    protected $dbWrapper = null;

    /**
     * the key to retrieve the login in the presistent session
     *
     * @access public
     * @var string
     */
    const LOGIN_KEY = 'user_login';

    /**
     * the key to retrieve the authentication token in the presistent session
     *
     * @access public
     * @var string
     */
    const AUTH_TOKEN_KEY = 'auth_id';

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function __construct()
    {
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D1E begin
		
		$this->dbWrapper = core_kernel_classes_DbWrapper::singleton(DATABASE_NAME);
		
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D1E end
    }

    /**
     * set the language for the user identified by the login in parameter
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string login
     * @param  string lang
     * @return boolean
     */
    public function setUserLanguage($login, $lang)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001CFC begin
		
		$lang = strtoupper($lang);
		
		if(!preg_match("/^[A-Z]{2,3}$/", $lang)){
			throw new Exception("Invalid lang code $lang");
		}
		if(count($this->getCurrentUser($login)) == 0){
			throw new Exception("Invalid user $login");
		}
		
		$returnValue = $this->dbWrapper->execSql("UPDATE `user` SET `Deflg` = '".$lang."' WHERE `user`.`login`  = '$login' LIMIT 1 ");
		
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001CFC end

        return (bool) $returnValue;
    }

    /**
     * get the language defined for the user identified by the login in
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string login
     * @return string
     */
    public function getUserLanguage($login)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D00 begin
		
		$result = $this->dbWrapper->execSql("SELECT `Deflg` FROM `user` WHERE `login`  = '$login'  LIMIT 1 ");
		if (!$result->EOF){
			$returnValue = $result->fields['Deflg'];
		}
		
		if(empty($returnValue)){
			$returnValue = $this->getDefaultLanguage();
		}
		
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D00 end

        return (string) $returnValue;
    }

    /**
     * get the language defined by default
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function getDefaultLanguage()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--56be7ee7:12579087097:-8000:0000000000001D16 begin
		
		$returnValue = strtoupper($this->dbWrapper->getSetting('Deflg'));
		
        // section 127-0-1-1--56be7ee7:12579087097:-8000:0000000000001D16 end

        return (string) $returnValue;
    }

    /**
     * retrieve the logged in user
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return array
     */
    public function getCurrentUser()
    {
        $returnValue = array();

        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D03 begin
		
		if(Session::hasAttribute(self::LOGIN_KEY)){
			$login = Session::getAttribute(self::LOGIN_KEY);
			if(strlen($login) > 0){
				$result = $this->dbWrapper->execSql("SELECT `user`.* FROM `user` WHERE `user`.`login` = '$login' LIMIT 1");
				if (!$result->EOF){
					$returnValue = $result->fields;
				}
			}
		}
		
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D03 end

        return (array) $returnValue;
    }

    /**
     * authenticate a user
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string login
     * @param  string password
     * @param  boolean checkAdmin
     * @return boolean
     */
    public function loginUser($login, $password, $checkAdmin = true)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D05 begin
		$query = "SELECT `user`.* FROM `user` 
			 WHERE `user`.`login`  = '".addslashes($login)."' 
			 AND `user`.`password` = '".md5($password)."' 
			 AND `user`.`enabled` = 1 ";
		if($checkAdmin){
			$query .= " AND `user`.`admin` = '1' ";
		}
		$query .= " LIMIT 1 ";
		
		$result = $this->dbWrapper->execSql($query);
		while (!$result->EOF){
			$foundLogin = $result->fields['login'];
			if(strlen($foundLogin) > 0){
				Session::setAttribute(self::LOGIN_KEY, $result->fields['login']);
				Session::setAttribute(self::AUTH_TOKEN_KEY, uniqid());
				$returnValue = true;
				break;
			}
			$result->MoveNext();
		}
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D05 end

        return (bool) $returnValue;
    }

    /**
     * check if a session is currently running
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return boolean
     */
    public static function isASessionOpened()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--5f8e44a2:1258d8ab867:-8000:0000000000001D20 begin
		if(Session::hasAttribute(self::AUTH_TOKEN_KEY) && Session::hasAttribute(self::LOGIN_KEY)){
			if(preg_match("/^[0-9a-f]{12,13}$/", strtolower(Session::getAttribute(self::AUTH_TOKEN_KEY)))){
				$returnValue = true;
			}
		}
        // section 127-0-1-1--5f8e44a2:1258d8ab867:-8000:0000000000001D20 end

        return (bool) $returnValue;
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
		
		$query = "SELECT `user`.* FROM `user` WHERE `enabled` = 1 ";
		if(isset($options['order'])){
			$query .= " ORDER BY {$options['order']} ";
			(isset($options['orderDir'])) ? $query .= $options['orderDir'] :  $query .= 'ASC';
		}
		if(isset($options['start']) && isset($options['end'])){
			$query .= " LIMIT {$options['start']}, {$options['end']} ";
		}
		$result = $this->dbWrapper->execSql($query);
		while (!$result->EOF){
			$returnValue[] = $result->fields;
			$result->MoveNext();
		}
		
        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D44 end

        return (array) $returnValue;
    }

    /**
     * get a user by his login
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string login the user login is the unique identifier to retrieve him
     * @return array
     */
    public function getOneUser($login)
    {
        $returnValue = array();

        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D4E begin
		
		$result = $this->dbWrapper->execSql("SELECT `user`.* FROM `user` WHERE `user`.`login`  = '".addslashes($login)."' LIMIT 1 ");
		if (!$result->EOF){
			$returnValue = $result->fields;
		}
		
        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D4E end

        return (array) $returnValue;
    }

    /**
     * Save (insert or update) the user in parameter
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array user
     * @return boolean
     */
    public function saveUser($user)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D53 begin
		
		if(isset($user['login'])){
			$admin = '1';
			if(isset($user['admin'])){
				$admin = $user['admin'];
			}
			if(count($this->getOneUser($user['login'])) == 0){
				//insert
				$returnValue = $this->dbWrapper->execSql(
					"INSERT INTO `user` (login, password, admin, usergroup, LastName, FirstName, E_Mail, Company, Deflg, Uilg, enabled) 
					VALUES ('{$user['login']}', '{$user['password']}', '{$admin}', 'admin', '{$user['LastName']}', '{$user['FirstName']}', '{$user['E_Mail']}', '{$user['Company']}', '{$user['Deflg']}', '{$user['Uilg']}', 1)"
				);
			}
			else{
				//update
				$returnValue = $this->dbWrapper->execSql(
					"UPDATE `user`  
					SET password = '{$user['password']}', admin = '{$admin}', LastName = '{$user['LastName']}', FirstName='{$user['FirstName']}', E_Mail='{$user['E_Mail']}', Company='{$user['Company']}', Deflg='{$user['Deflg']}', Uilg='{$user['Uilg']}' 
					WHERE login = '{$user['login']}'"
				);
			}
		}
        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D53 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method removeUser
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string login
     * @return boolean
     */
    public function removeUser($login)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D56 begin
		if(strlen($login) > 0){
			$returnValue = $this->dbWrapper->execSql("DELETE FROM `user` WHERE login = '{$login}' LIMIT 1");
		}
        // section 127-0-1-1--54120360:125930cf6af:-8000:0000000000001D56 end

        return (bool) $returnValue;
    }

    /**
     * Remove a user by a login
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
		
		$returnValue = true ;
		
		$result = $this->dbWrapper->execSql("SELECT COUNT(login) as number FROM `user` WHERE `user`.`login`  = '".addslashes($login)."' AND `user`.`enabled` = 1");
		if (!$result->EOF){
			($result->fields['number'] == 0) ? $returnValue = false : $returnValue = true ;
		}
		
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

} /* end of class tao_models_classes_UserService */

?>