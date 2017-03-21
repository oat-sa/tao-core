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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */
namespace oat\tao\helpers\form\elements\xhtml;

use oat\tao\helpers\form\elements\Validators as AbstractValidators;

/**
 * Based on tao_helpers_form_elements_xhtml_Radiobox
 */
class Validators extends AbstractValidators
{
    use XhtmlRenderingTrait;

    /**
     *
     * @see tao_helpers_form_FormElement::render
     */
    public function render()
    {
        $returnValue = $this->renderLabel();
        
        $i = 0;
        $returnValue .= '<div class="form_radlst form_checklst">';
        foreach ($this->getOptions() as $optionId => $optionLabel) {
            $returnValue .= "<input type='checkbox' value='{$optionId}' name='{$this->name}[]' id='{$this->name}_{$i}' ";
            $returnValue .= $this->renderAttributes();
        
            if (in_array($optionId, $this->values)) {
                $returnValue .= " checked='checked' ";
            }
            $returnValue .= " />&nbsp;<label class='elt_desc' for='{$this->name}_{$i}'>" . _dh($optionLabel) . "</label><br />";
            $i ++;
        }
        $returnValue .= "</div>";
        
        return (string) $returnValue;
    }
}
