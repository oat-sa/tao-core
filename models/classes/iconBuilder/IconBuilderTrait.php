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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\iconBuilder;

trait IconBuilderTrait
{
    /**
     * This function builds the actual HTML element and is used by all other functions.
     * The doc for $options is the applicable for all other functions.
     *
     * @param string $icon    name of the icon to display
     * @param array  $options (optional) hashtable with HTML attributes, also allows to set element="almostAnyHtmlElement"
     *
     * @return string HTML element with icon
     */
    protected static function buildIcon($icon, array $options = [])
    {
        $dom = new DOMDocument();

        $element = $options['element'];
        unset($options['element']);

        $options['class'] = trim(implode(' ', [$options['icon'], $icon]));

        $element = $dom->createElement($element ?? 'span');
        foreach ($options as $key => $value) {
            $element->setAttribute($key, $value);
        }

        return $dom->saveHTML($element);
    }
}
