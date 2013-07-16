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
 * A helper to fascilitate the exchange of data between php and javascript
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
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
     * @author Joel Bout, <joel@taotesting.com>
     * @param  array array
     * @return string
     */
    public static function buildObject($array)
    {
        $returnValue = (string) '';

        if (count($array) == 0) {
			$returnValue = '{}';
        } else {
			$returnValue = '{';
			foreach ($array as $k => $v) {
				$returnValue .= '\''.$k.'\':'.(is_array($v) ? self::buildObject($v): json_encode($v)).',';
			}
        }
		$returnValue =  substr($returnValue, 0, -1).'}';

        return (string) $returnValue;
    }

}