<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/models/classes/class.UserService.php
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 14.12.2009, 15:19:40 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/models/classes/class.Service.php');

/* user defined includes */
// section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001CFA-includes begin
// section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001CFA-includes end

/* user defined constants */
// section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001CFA-constants begin
// section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001CFA-constants end

/**
 * Short description of class tao_models_classes_UserService
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_UserService
    extends tao_models_classes_Service
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute dbWrapper
     *
     * @access protected
     * @var DbWrapper
     */
    protected $dbWrapper = null;

    /**
     * Short description of attribute LOGIN_KEY
     *
     * @access public
     * @var string
     */
    const LOGIN_KEY = 'user_login';

    /**
     * Short description of attribute AUTH_TOKEN_KEY
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
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return mixed
     */
    public function __construct()
    {
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D1E begin
		
		$this->dbWrapper = core_kernel_classes_DbWrapper::singleton(DATABASE_NAME);
		
        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D1E end
    }

    /**
     * Short description of method setUserLanguage
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * Short description of method getUserLanguage
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * Short description of method getDefaultLanguage
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * Short description of method getCurrentUser
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * Short description of method loginUser
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string login
     * @param  string password
     * @return boolean
     */
    public function loginUser($login, $password)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-37d8f507:12577bc7e88:-8000:0000000000001D05 begin
		
		$result = $this->dbWrapper->execSql(
			"SELECT `user`.* FROM `user` 
			 WHERE `user`.`login`  = '".addslashes($login)."' 
			 AND `user`.`password` = '".md5($password)."' 
			 LIMIT 1 "
		);
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
     * Short description of method isASessionOpened
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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

} /* end of class tao_models_classes_UserService */

?>