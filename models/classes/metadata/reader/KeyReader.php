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
 * Copyright (c) 2016 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\metadata\reader;

/**
 * Class KeyReader, read a $key of given $data
 * @package oat\tao\model\metadata\reader
 */
class KeyReader implements Reader
{
    protected $key;
    protected $alias;

    /**
     * KeyReader constructor.
     *
     * @param string $alias Alias of value to find
     * @param string $key Index to find
     * @throws \Exception
     */
    public function __construct($alias, $key)
    {
        if (! is_string($alias) || ! is_string($key)) {
            throw new \Exception();

        }
        $this->alias = $alias;
        $this->key = $key;
    }

    /**
     * Get value of $data using $alias or $key
     *
     * @todo if null value  ? Throw specific exception ?
     *
     * @param array $data
     * @return mixed|null
     */
    public function getValue(array $data)
    {
        if ($this->hasValue($data, $this->alias)) {
            return $data[$this->alias];
        }
        if ($this->hasValue($data, $this->key)) {
            return $data[$this->key];

        }
        return null;
    }

    /**
     * Check $key of $data array exists
     *
     * @param array $data
     * @param $key
     * @return bool
     */
    protected function hasValue(array $data, $key)
    {
        return isset($data[$key]);
    }
}