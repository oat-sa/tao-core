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

/**
 * Trait to render the label
 */
trait XhtmlRenderingTrait
{

    public function renderLabel()
    {
        $renderedLabel = '';
        if (! isset($this->attributes['noLabel']) && !empty($this->description)) {
            $renderedLabel .= "<label class='form_desc' for='" . $this->name . "'>" . _dh($this->getDescription());
            if (isset($this->attributes['required'])) {
                $renderedLabel .= "<abbr title='" . __('This field is required') . "'>*</abbr>";
                unset($this->attributes['required']);
            }
            ;
            $renderedLabel .= "</label>";
        } else {
            unset($this->attributes['noLabel']);
        }
        return (string) $renderedLabel;
    }
}
