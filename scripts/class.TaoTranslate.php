<?php

error_reporting(E_ALL);

/**
 * TAO - tao\scripts\class.TaoTranslate.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 05.01.2012, 15:50:10 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
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
require_once('tao/scripts/class.Runner.php');

/* user defined includes */
// section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003286-includes begin
// section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003286-includes end

/* user defined constants */
// section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003286-constants begin
// section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003286-constants end

/**
 * Short description of class tao_scripts_TaoTranslate
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage scripts
 */
class tao_scripts_TaoTranslate
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
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003287 begin
    $this->options = array('verbose' => false,
        					   'action' => null);
        
        $this->options = array_merge($this->options, $this->parameters);
        
        if ($this->options['verbose'] == true) {
        	$this->verbose = true;
        } else {
        	$this->verbose = false;
        }
        
        // The 'action' parameter is always required.
        if ($this->options['action'] == null) {
        	self::err("Please enter the 'action' parameter.", true);
        } else {
        	$this->action = strtolower($action);
        	$allowedActions = array('create',
        							'update',
        							'delete',
        							'updateall',
        							'deleteall');
        	
        	if (!in_array($this->options['action'], $allowedActions)) {
        		self::err("Please enter a valid 'action' parameter.", true);
        	} else {
        		
        	}
        }
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003287 end
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
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003289 begin
        
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:0000000000003289 end
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
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:000000000000328B begin
        // section -64--88-1-7-6b37e1cc:1336002dd1f:-8000:000000000000328B end
    }

    /**
     * Short description of method checkInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    private function checkInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003840 begin
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003840 end
    }

    /**
     * Short description of method checkCreateInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    private function checkCreateInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003842 begin
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003842 end
    }

    /**
     * Short description of method checkUpdateInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    private function checkUpdateInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003844 begin
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003844 end
    }

    /**
     * Short description of method checkUpdateAllInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    private function checkUpdateAllInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003846 begin
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003846 end
    }

    /**
     * Short description of method checkDeleteInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    private function checkDeleteInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003848 begin
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:0000000000003848 end
    }

    /**
     * Short description of method checkDeleteAllInput
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    private function checkDeleteAllInput()
    {
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:000000000000384A begin
        // section 10-13-1-85--7b8e6d0a:134ae555568:-8000:000000000000384A end
    }

} /* end of class tao_scripts_TaoTranslate */

?>