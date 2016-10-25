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
use oat\tao\model\metadata\exception\reader\MetadataReaderNotFoundException;

/**
 * Class KeyReader, read a $key of given $data
 *
 * @author Camille Moyon
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
        $this->alias = $alias;
        $this->key = $key;
    }

    /**
     * Get value of $data using $alias or $key
     *
     * @param array $data A CSV line
     * @return string
     * @throws MetadataReaderNotFoundException
     */
    public function getValue(array $data)
    {
        if ($this->hasValue($data, $this->alias)) {
            return $data[$this->alias];
        }
        if ($this->hasValue($data, $this->key)) {
            return $data[$this->key];
        }

        throw new MetadataReaderNotFoundException(
            __CLASS__ . ' cannot found value associated to key "' . $this->key . '" or alias "' . $this->alias . '"'
        );
    }

    /**
     * Check if $key of $data array exists
     *
     * @param array $data
     * @param $key
     * @return bool
     */
    protected function hasValue(array $data, $key)
    {
        if (! is_string($key)) {
            return false;
        }
        return isset($data[$key]);
    }
}