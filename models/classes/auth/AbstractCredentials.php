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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\auth;

/**
 * Abstract class for a credential object
 *
 * Class AbstractCredentials
 * @package oat\tao\model\auth
 */
abstract class AbstractCredentials
{

    /** @var array  */
    protected $properties;

    /**
     * @return array
     */
    abstract public function getProperties();

    /**
     * AbstractCredentials constructor.
     * @param array $properties
     * @throws \common_exception_ValidationFailed
     */
    public function __construct($properties = [])
    {
        $this->properties = $properties;
        $this->validate($properties);
    }

    /**
     * @throws \common_exception_ValidationFailed
     */
    protected function validate()
    {
        $extraProperties = array_diff_key($this->properties, $this->getProperties());
        if ($extraProperties) {
            throw new \common_exception_ValidationFailed(reset($extraProperties));
        }
    }
}
