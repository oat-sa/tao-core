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
 * Short description of class tao_helpers_form_elements_xhtml_Textbox
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 
 */
class tao_helpers_form_elements_xhtml_Textbox
    extends tao_helpers_form_elements_Textbox
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method render
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function render()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018E8 begin
		
		if(!isset($this->attributes['noLabel'])){
			$returnValue .= "<label class='form_desc' for='{$this->name}'>". _dh($this->getDescription())."</label>";
		}
		else{
			unset($this->attributes['noLabel']);
		}
		$returnValue .= "<input type='text' name='{$this->name}' id='{$this->name}' ";
		$returnValue .= $this->renderAttributes();
		$returnValue .= ' value="'._dh($this->value).'" />';
		
		if(!empty($this->unit)){
			$returnValue .= " " . _dh($this->unit);
		}
		
        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018E8 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_Textbox */

?>
