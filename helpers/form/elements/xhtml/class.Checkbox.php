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
 * Short description of class tao_helpers_form_elements_xhtml_Checkbox
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 */
class tao_helpers_form_elements_xhtml_Checkbox extends tao_helpers_form_elements_Checkbox
{
    use XhtmlRenderingTrait;
        
    /**
     * Short description of method feed
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     */
    public function feed()
    {
        $expression = "/^" . preg_quote($this->name, "/") . "(.)*[0-9]+$/";
        $this->setValues(array());
        foreach ($_POST as $key => $value) {
            if (preg_match($expression, $key)) {
                $this->addValue(tao_helpers_Uri::decode($value));
            }
        }
    }

    /**
     * Short description of method render
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function render()
    {
        $returnValue = $this->renderLabel();
        
        $checkAll = false;
        if (isset($this->attributes['checkAll'])) {
            $checkAll = (bool) $this->attributes['checkAll'];
            unset($this->attributes['checkAll']);
        }
        $i = 0;
        $checked = 0;
        $returnValue .= '<div class="form_radlst form_checklst plain">';
        $readOnlyOptions = $this->getReadOnly();
        foreach ($this->options as $optionId => $optionLabel) {
            $readOnly = isset($readOnlyOptions[$optionId]);
            if($readOnly){
                $returnValue .= '<div class="grid-row readonly">';
            }else{
                $returnValue .= '<div class="grid-row">';
            }

            $returnValue .= '<div class="col-1">';
            $returnValue .= "<input type='checkbox' value='{$optionId}' name='{$this->name}_{$i}' id='{$this->name}_{$i}' ";
            $returnValue .= $this->renderAttributes();

            if ($readOnly) {
                $returnValue .= "disabled='disabled' readonly='readonly' ";
            }

            if (in_array($optionId, $this->values)) {
                $returnValue .= " checked='checked' ";
                $checked ++;
            }
            $returnValue .= ' />';
            $returnValue .= '</div><div class="col-10">';
            $returnValue .= "<label class='elt_desc' for='{$this->name}_{$i}'>" . _dh($optionLabel) . "</label>";
            $returnValue .= '</div><div class="col-1">';
            if ($readOnly) {
                $readOnlyReason = $readOnlyOptions[$optionId];
                if(!empty($readOnlyReason)){
                    $returnValue .= '<span class="tooltip-trigger icon-warning" data-tooltip="~ .tooltip-content" data-tooltip-theme="info"></span><div class="tooltip-content">'._dh($readOnlyReason).'</div>';
                }
            }
            $returnValue .= '</div></div>';
            $i ++;
        }
        $returnValue .= "</div>";
        
        // add a small link
        if ($checkAll) {
            if ($checked == (count($this->options) - count($readOnlyOptions))) {
                $returnValue .= "<span class='checker-container'><a id='{$this->name}_checker' class='box-checker box-checker-uncheck' href='#'>" . __('Uncheck All') . "</a></span>";
            } else {
                $returnValue .= "<span class='checker-container'><a id='{$this->name}_checker' class='box-checker' href='#'>" . __('Check All') . "</a></span>";
            }
        }
        
        return (string) $returnValue;
    }

    /**
     * Short description of method setValue
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param string $value
     * @return mixed
     */
    public function setValue($value)
    {
        $this->addValue($value);
    }

    /**
     * Short description of method getEvaluatedValue
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function getEvaluatedValue()
    {
        return array_map("tao_helpers_Uri::decode", $this->getValues());
        // return array_map("tao_helpers_Uri::decode", $this->getRawValue());
    }
}
