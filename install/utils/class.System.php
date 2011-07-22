<?php
class tao_install_utils_System{
	
	/**
	 * Get informations on the system
	 * @return array
	 */
	public static function getInfos(){
                
                
		//subfolder shall be detected as /SUBFLODERS/tao/install/index.php so we remove the "/extension/module/action" part:
                $subfolder = $_SERVER['REQUEST_URI'];
                $subfolder = preg_replace('/\/(([^\/]*)\/){2}([^\/]*)$/', '', $subfolder);
                $subfolder = preg_replace('/^\//', '', $subfolder);
                
                return array(
			'folder'	=> $subfolder,
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