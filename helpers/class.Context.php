<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * The context class enables you to define some context to the application
 * and to check statically which context/mode is actually loaded.
 *
 * @access public
 * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Context
{

    /**
     * The list of current loaded modes. This array contains strings. 
     *
     * @access protected
     * @var array
     */
    protected static $current = array();


    /**
     * load a new current mode
     *
     * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string mode
     */
    public static function load($mode)
    {
		if(!is_string($mode)){
			throw new Exception("Try to load an irregular mode in the context");
		}
    	if(empty($mode)){
    		throw new Exception("Cannot load an empty mode in the context");
    	}
    	
    	if(!in_array($mode, self::$current)){
    		self::$current[] = $mode;
    	}
    }

    /**
     * check if the mode in parameter is loaded in the context.
     *
     * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string mode The mode you want to check it is loaded or not.
     * @return boolean
     */
    public static function check($mode)
    {
        $returnValue = (bool) false;
        
    	if(!is_string($mode)){
			throw new Exception("Try to check an irregular mode");
		}
    	if(empty($mode)){
    		throw new Exception("Cannot check an empty mode");
    	}
    	
    	$returnValue = in_array($mode, self::$current);

        return (bool) $returnValue;
    }

    /**
     * reset the context.
     *
     * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
     */
    public static function reset()
    {
    	self::$current = array();
    }

    /**
     * Unload a specific mode.
     *
     * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string mode
     */
    public static function unload($mode)
    {

    	if(!is_string($mode)){
			throw new Exception("Try to unload an irregular mode in the context");
		}
    	if(empty($mode)){
    		throw new Exception("Cannot unload an empty mode in the context");
    	}
    	
    	if(in_array($mode, self::$current)){
    		$index = array_search ($mode, self::$current);
    		if ($index !== false){
    			unset (self::$current[$index]);
    		}
    	}
    }
}

?>