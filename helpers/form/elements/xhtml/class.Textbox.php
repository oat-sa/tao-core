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
use oat\tao\helpers\form\elements\xhtml\XhtmlRenderingTrait;

/**
 * Simple Textbox to enter literals
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 */
class tao_helpers_form_elements_xhtml_Textbox extends tao_helpers_form_elements_Textbox
{
    use XhtmlRenderingTrait;

    /**
     * (non-PHPdoc)
     * 
     * @see tao_helpers_form_FormElement::render()
     */
    public function render()
    {
        $returnValue = $this->renderLabel();
        
        $hasUnit = ! empty($this->unit);
        
        if ($hasUnit) {
            $this->addClass('has-unit');
        }
        $returnValue .= "<input type='text' name='{$this->name}' id='{$this->name}' ";
        $returnValue .= $this->renderAttributes();
        $returnValue .= ' value="' . _dh($this->value) . '" />';
        
        if ($hasUnit) {
            $returnValue .= '<label class="unit" for="' . $this->name . '">' . _dh($this->unit) . '</label>';
        }
        
        return (string) $returnValue;
    }
}
