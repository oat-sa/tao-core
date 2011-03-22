<?php
class tao_install_utils_System{
	
	/**
	 * Get informations on the system
	 * @return array
	 */
	public static function getInfos(){
		return array(
			'folder'	=> '',
			'host'		=> $_SERVER['HTTP_HOST'],
			'https'		=> ($_SERVER['SERVER_PORT'] == 443) 
		);
	}
	
	/**
	 * Check if TAO is already installed
	 * @return boolean
	 */
	public static function isTAOInstalled(){
		$config = realpath(dirname(__FILE__).'/../../../generis/common/config.php');
		return (file_exists($config));
	}
}
?>