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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\textConverter;

use oat\oatbox\service\ConfigurableService;

abstract class TextConverterService extends ConfigurableService
{
    /**
     * Return the list of text to convert
     *
     * @return array
     */
    abstract public function getTextRegistry();

    /**
     * Return the associated conversion of the given key
     *
     * @param string $key The text to convert, should be an index of getTextRegistry array
     * @return string
     */
    public function get($key)
    {
        $textRegistry = $this->getTextRegistry();
        if (is_string($key) && isset($textRegistry[$key])) {
            return $textRegistry[$key];
        }
        return $key;
    }

}