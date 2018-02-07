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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */
namespace oat\tao\model\search\index;

/**
 * Class IndexProperty
 * @package oat\tao\model\search\index
 */
class IndexProperty
{
    /** @var string */
    protected $field;

    /** @var boolean */
    protected $fuzzy;

    /** @var boolean */
    protected $default;

    /**
     * IndexProperty constructor.
     * @param $field
     * @param bool $fuzzy
     * @param bool $default
     * @throws \common_Exception
     */
    public function __construct(
        $field,
        $fuzzy = false,
        $default = false
    ){
        $this->field = $field;
        $this->fuzzy = $fuzzy;
        $this->default = $default;

    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return bool
     */
    public function isFuzzy()
    {
       return (boolean) $this->fuzzy;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return (boolean) $this->default;
    }

}
