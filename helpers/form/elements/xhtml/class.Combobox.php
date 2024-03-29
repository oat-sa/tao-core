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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

use oat\tao\helpers\form\elements\xhtml\XhtmlRenderingTrait;

/**
 * Short description of class tao_helpers_form_elements_xhtml_Combobox
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 */
class tao_helpers_form_elements_xhtml_Combobox extends tao_helpers_form_elements_Combobox
{
    use XhtmlRenderingTrait;

    /**
     * Short description of method render
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function render()
    {
        $returnValue = $this->renderLabel();

        $returnValue .= "<select name='{$this->name}' id='{$this->name}' data-testid='{$this->getDescription()}' ";
        $returnValue .= $this->renderAttributes();
        $returnValue .= '>';
        if (!empty($this->emptyOption)) {
            $this->options = array_merge(
                [
                    ' ' => $this->emptyOption
                ],
                $this->options
            );
        }
        $encodedValue = (string)$this->value;

        foreach ($this->options as $optionId => $optionLabel) {
            $returnValue .= "<option value='{$optionId}' {$this->parseOptionAttributes($optionId)}";
            if ($encodedValue === $optionId) {
                $returnValue .= " selected='selected' ";
            }
            $returnValue .= '>' . _dh($optionLabel) . '</option>';
        }
        $returnValue .= '</select>';

        return $returnValue;
    }

    private function parseOptionAttributes(string $option): string
    {
        $output = '';

        foreach ($this->getOptionAttributes($option) as $attribute => $value) {
            $output = sprintf(' %s="%s"', $attribute, $value);
        }

        return $output;
    }
}
