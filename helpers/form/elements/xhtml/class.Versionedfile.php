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
 * Old widget to display file properties
 *
 * @access public
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @deprecated
 */
class tao_helpers_form_elements_xhtml_Versionedfile extends tao_helpers_form_elements_Versionedfile
{
    use XhtmlRenderingTrait;

    /**
     * Short description of attribute CSS_CLASS
     *
     * @access protected
     * @var string
     */
    const CSS_CLASS = 'editVersionedFile';
    
    /**
     * Short description of method render
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @return string
     */
    public function render()
    {
        
        if (array_key_exists('class', $this->attributes)) {
            if (strstr($this->attributes['class'], self::CSS_CLASS) !== false) {
                $this->attributes['class'] .= ' ' . self::CSS_CLASS;
            }
        } else {
            $this->attributes['class'] = self::CSS_CLASS;
        }
        
        $returnValue = $this->renderLabel();
        
        $returnValue .= "<input type='button' for='{$this->name}' value='" . __('Manage Versioned File') . "' ";
        $returnValue .= $this->renderAttributes();
        $returnValue .= " />";
        $returnValue .= "<span for='{$this->name}' " . $this->renderAttributes() . "></span>";
        
        return (string) $returnValue;
    }
}
