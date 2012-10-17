<?php

error_reporting(E_ALL);

/**
 * TAO - tao/scripts/class.TaoInstall.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 11.07.2011, 18:41:26 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage scripts
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_scripts_Runner
 *
 * @author firstname and lastname of author, <author@example.org>
 */

/* user defined includes */
// section 127-0-1-1--109d2719:1311a0f963b:-8000:0000000000002E71-includes begin
// section 127-0-1-1--109d2719:1311a0f963b:-8000:0000000000002E71-includes end

/* user defined constants */
// section 127-0-1-1--109d2719:1311a0f963b:-8000:0000000000002E71-constants begin
// section 127-0-1-1--109d2719:1311a0f963b:-8000:0000000000002E71-constants end

/**
 * Short description of class tao_scripts_TaoInstall
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage scripts
 */
class tao_scripts_TaoInstall
    extends tao_scripts_Runner
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method preRun
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function preRun()
    {
        // section 127-0-1-1--109d2719:1311a0f963b:-8000:0000000000002E76 begin
        
    	$this->options = array (
			"db_driver"	=>			"mysql"
			, "db_host"	=>			"localhost"
			, "db_name"	=>			"mytao"
			, "db_pass"	=>			"tao"
			, "db_user"	=>			"tao"
			, "install_sent"	=>	"1"
			, "module_host"	=>		"tao.local"
			, "module_lang"	=>		"EN"
			, "module_mode"	=>		"debug"
			, "module_name"	=>		"mytao"
			, "module_namespace" =>	""
			, "module_url"	=>		""
			, "submit"	=>			"Install"
			, "user_email"	=>		""
			, "user_firstname"	=>	""	
			, "user_lastname"	=>	""
			, "user_login"	=>		""
			, "user_pass"	=>		""
			, "import_local" => 	true
			, "instance_name" =>	"tao"
		);
        
    	$this->options = array_merge($this->options, $this->parameters);
        
        if ($this->options['import_local'] == true){
            $this->options['import_local'] = array('on');
        }
        else{
            $this->options['import_local'] = array('off');
        }
    	
    	// user password treatment
    	$this->options["user_pass1"] = $this->options['user_pass'];
    	// module namespace generation
    	if (empty ($this->options["module_namespace"])){
    		$this->options['module_namespace'] = 'http://'.$this->options['module_host'].'/'.$this->options['module_name'].'.rdf';
    	}
    	
        // section 127-0-1-1--109d2719:1311a0f963b:-8000:0000000000002E76 end
    }

    /**
     * Short description of method run
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function run()
    {
        // section 127-0-1-1--109d2719:1311a0f963b:-8000:0000000000002E78 begin
        
        $rootDir = dir(dirname(__FILE__).'/../../');
		$root = realpath($rootDir->path).'/';
        $installator = new tao_install_Installator (array(
			'root_path' 	=> $root,
			'install_path'	=> dirname(__FILE__).'/../install/'
		));
		
		$installator->install ($this->options);
		
        // section 127-0-1-1--109d2719:1311a0f963b:-8000:0000000000002E78 end
    }

    /**
     * Short description of method postRun
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function postRun()
    {
        // section 127-0-1-1--109d2719:1311a0f963b:-8000:0000000000002E7A begin
        // section 127-0-1-1--109d2719:1311a0f963b:-8000:0000000000002E7A end
    }

} /* end of class tao_scripts_TaoInstall */

?>
