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

use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\metadata\exception\reader\MetadataReaderNotFoundException;

/**
 * Class KeyReader, read a $key of given $data
 *
 * @author Camille Moyon
 * @package oat\tao\model\metadata\reader
 */
class KeyReader implements Reader
{
    /**
     * Source key label to find into $dataSource array
     */
    const KEY_SOURCE = 'key';

    protected $key;

    /**
     * KeyReader constructor.
     *
     * @param string $key Index to find
     * @throws \Exception
     */
    public function __construct($options)
    {
        if (! is_array($options)) {
            throw new InconsistencyConfigException('Reader options has to be an array.');
        }

        if (! array_key_exists(self::KEY_SOURCE, $options)) {
            throw new InconsistencyConfigException('Missing configuration keys for reader, attribute "' . self::KEY_SOURCE . '" not found');
        }

        $this->key = $options[self::KEY_SOURCE];
    }

    /**
     * Get value of $data using $key
     *
     * @param array $data A CSV line
     * @return string
     * @throws MetadataReaderNotFoundException
     */
    public function getValue(array $data)
    {
        $key = strtolower($this->key);
        if ($this->hasValue($data, $key)) {
            return $data[$key];
        }

        throw new MetadataReaderNotFoundException(__CLASS__ . ' cannot found value associated to key "' . $this->key . '".');
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

    /**
     * Configuration serialization
     *
     * @return array
     */
    public function __toPhpCode()
    {
        $options = array('key' => $this->key);
        return empty($options) ? '' : \common_Utils::toHumanReadablePhpString($options);
    }

}