<?php
/**  
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
 * Short description of class tao_helpers_form_validators_Integer
 *
 * @access public
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
 * @package tao
 
 */
class tao_helpers_form_validators_Integer
    extends tao_helpers_form_validators_Numeric
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AAC begin
				parent::__construct($options);
        // section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AAC end
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  values
     * @return boolean
     */
    public function evaluate($values)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AB6 begin
				if ($values == intval($values)) {
					$returnValue = parent::evaluate($values);
				} else {
					$returnValue = false;
					$this->message .= __('The value of this field must be an integer');
				}
        // section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AB6 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_Integer */

?>