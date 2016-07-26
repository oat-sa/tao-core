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
 *               2013
 */

/**
 * An XHTML Form Element enabling the edition of JSON strings.
 *
 */
class tao_helpers_form_elements_xhtml_JsonObject
    extends tao_helpers_form_elements_JsonObject
{
    /**
     * Render the JSON String as a collection of key/value pairs
     *
     * @return string
     */
    public function render()
    {
        $returnValue = '';
		
		if (!isset($this->attributes['noLabel'])) {
			$returnValue .= "<label class=\"form_desc\" for=\"{$this->name}\">" . _dh($this->getDescription()) . "</label>";
		} else {
			unset($this->attributes['noLabel']);
		}
        
        if (empty($this->value) === true) {
            // @todo should be in a blue info box.
            $returnValue .= "No values to display.";
        } elseif (($jsonObject = @json_decode($this->value)) === null) {
            // @todo should be in a red error box.
            $returnValue .= "Invalid value.";
        } else {
            // Valid JSON to be displayed.
            $returnValue .= "<ul class=\"json-object-list\" style=\"width: 65%; display: inline-block; list-style-type: none; padding: 0;\">";

            foreach ($jsonObject as $jsonKey => $jsonValue) {
                $returnValue .= "<li style=\"margin-bottom: 10px;\">";
                
                $returnValue .= "<div class=\"widget-jsonobject-key\">" . _dh($jsonKey) . ":</div>";
                $returnValue .= "<div><input class=\"widget-jsonobject-value\" type=\"text\" disabled=\"disabled\" value=\"${jsonValue}\" style=\"width: 100%;\"/></</div>";
                
                $returnValue .= "</li>";
            }
            
            $returnValue .= "</ul>\n";
        }
		
        return $returnValue;
    }
}
