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
 * Short description of class tao_helpers_form_elements_xhtml_Htmlarea
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 */
class tao_helpers_form_elements_xhtml_Htmlarea extends tao_helpers_form_elements_Htmlarea
{
    use XhtmlRenderingTrait;

    /**
     * Short description of attribute CSS_CLASS
     *
     * @access public
     * @var string
     */
    public const CSS_CLASS = 'html-area';

    /**
     * Short description of method render
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
        $returnValue .= "<textarea name='{$this->name}' id='{$this->name}' data-testid='{$this->getDescription()}' ";
        $returnValue .= $this->renderAttributes();
        $returnValue .= ">" . _dh($this->value) . "</textarea>";


        return (string) $returnValue;
    }

    /**
     * Get the HtmlArea value, XSS sanitized
     * @return string the sanitized value
     */
    public function getEvaluatedValue()
    {
        return tao_helpers_Display::sanitizeXssHtml($this->getRawValue());
    }
}
