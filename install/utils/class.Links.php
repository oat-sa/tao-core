<?php
class tao_install_utils_Links{
	
	public static function buildBackendLink($moduleUrl) {
		return self::buildLink($moduleUrl, 'tao');
	}
	
	public static function buildFrontendLink($moduleUrl) {
		return self::buildLink($moduleUrl, 'test');
	}
	
	private static function buildLink($moduleUrl, $target) {
		if (substr($moduleUrl, -1) != '/') {
			$moduleUrl .= '/';
		}
		
		return $moduleUrl . $target;
	}
	
}
?>