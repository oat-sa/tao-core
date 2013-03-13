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
?>
<?php

error_reporting(E_ALL);

/**
 * Utility class on Arrays
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-1eeb40:1264b38a9fa:-8000:0000000000001E16-includes begin
// section 127-0-1-1-1eeb40:1264b38a9fa:-8000:0000000000001E16-includes end

/* user defined constants */
// section 127-0-1-1-1eeb40:1264b38a9fa:-8000:0000000000001E16-constants begin
// section 127-0-1-1-1eeb40:1264b38a9fa:-8000:0000000000001E16-constants end

/**
 * Utility class on Arrays
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Array
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method sortByField
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array input
     * @param  string field
     * @param  boolean descending
     * @return array
     */
    public static function sortByField($input, $field, $descending = false)
    {
        $returnValue = array();

        // section 127-0-1-1-1eeb40:1264b38a9fa:-8000:0000000000001E17 begin
		
		$sorted = array();
		foreach($input as $key => $value ){
			$sorted[$key] = $value[$field];
		}

		if($descending){
			arsort($sorted);
		}
		else{
			asort($sorted);
		}

		foreach ($sorted as $key => $value ){
			$returnValue[$key] = $input[$key];
		}
		
        // section 127-0-1-1-1eeb40:1264b38a9fa:-8000:0000000000001E17 end

        return (array) $returnValue;
    }

} /* end of class tao_helpers_Array */

?>