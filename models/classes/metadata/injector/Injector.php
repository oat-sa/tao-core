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

namespace oat\tao\model\metadata\injector;

/**
 * Interface Injector
 *
 * @author Camille Moyon
 * @package oat\tao\model\metadata\injector
 */
interface Injector
{
    /**
     * Read all values from readers
     *
     * @param array $dataSource
     * @return array
     */
    public function read(array $dataSource);

    /**
     * Write a $data value to a $resource using writers
     *
     * @param \core_kernel_classes_Resource $resource
     * @param array $data
     * @param boolean $dryrun If true, no value will be wrote
     */
    public function write(\core_kernel_classes_Resource $resource, array $data, $dryrun = false);

    /**
     * Set readers and writers from $options
     */
    public function createInjectorHelpers();
}