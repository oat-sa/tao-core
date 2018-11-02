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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\helpers\metadata;

/**
 * Class ResourceCompiledMetadataHelper
 * @package oat\tao\helpers\metadata
 */
class ResourceCompiledMetadataHelper
{
    /**
     * Compiled resource metadata.
     *
     * @var array
     */
    private $metaData = [];

    /**
     * Gets a particular value from compiled metadata of a Resource.
     *
     * In case of no value can be found with given $language,
     * the implementation will try to retrieve a value for the default installation
     * language. Otherwise, the method returns NULL.
     *
     * @param string $language
     * @param string $name
     * @return mixed
     */
    public function getValue($name)
    {
        return isset($this->metaData[$name]) ? $this->metaData[$name] : null;
    }

    /**
     * Return resource label
     *
     * @return string|null
     */
    public function getLabel() {
        return $this->getValue('label');
    }

    /**
     * Unpacks resource metadata from a string.
     *
     * @param string $data
     * @return array
     * @throws \common_exception_InconsistentData
     */
    public function unserialize($data)
    {
        if (!is_string($data)) {
            throw new \common_exception_InconsistentData('The encoded resource metadata should be provided as a string');
        }

        $metaData = json_decode($data, true);

        if (!is_array($metaData)) {
            throw new \common_exception_InconsistentData('The decoded resource metadata should be an array');
        }
        $this->metaData = $metaData;

        return $this->metaData;
    }
}
