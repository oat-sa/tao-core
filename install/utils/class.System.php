<?php
class tao_install_utils_System{
	
	
	public static function getInfos(){
		return array(
			'folder'	=> '',
			'host'		=> $_SERVER['HTTP_HOST'],
			'https'		=> ($_SERVER['SERVER_PORT'] == 443) 
		);
	}
	
}
?>