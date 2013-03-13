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
 * TAO - tao/helpers/class.Javascript.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 23.08.2012, 17:55:26 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B91-includes begin
// section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B91-includes end

/* user defined constants */
// section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B91-constants begin
// section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B91-constants end

/**
 * Short description of class tao_helpers_Javascript
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Javascript
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * converts a php array into a js array
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  array array
     * @return string
     */
    public static function buildObject($array)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B92 begin
        if (count($array) == 0) {
			$returnValue = '{}';
        } else {
			$returnValue = '{';
			foreach ($array as $k => $v) {
				$returnValue .= '\''.$k.'\':'.(is_array($v) ? self::buildObject($v): '\''.$v.'\'').',';
			}
        }
		$returnValue =  substr($returnValue, 0, -1).'}';
        // section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B92 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_Javascript */

?>