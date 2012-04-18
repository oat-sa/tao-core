<?php
/**
 * 
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 */
class tao_actions_Resource extends tao_actions_CommonModule{
	
	/**
	 * constructor. Initialize the context
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	public function data(){
		var_dump($_SERVER);
		exit;
	}
}
?>