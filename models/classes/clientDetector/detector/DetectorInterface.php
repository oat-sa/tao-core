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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\clientDetector\detector;

/**
 * Interface DetectorInterface
 *
 * An interface to constraint interface to client clientDetector
 *
 * @package oat\tao\model\clientDetector\clientDetector
 */
interface DetectorInterface
{
    /**
     * Get text interpretation of detected client
     *
     * @return string
     */
    public function getClientName();

    /**
     * Get version of detected client
     *
     * @return string
     */
    public function getClientVersion();

    /**
     * Get the resource associated to the detected client
     *
     * @return \core_kernel_classes_Resource|null
     */
    public function getClientNameResource();

    /**
     * Get the property related to detected client name
     *
     * @return \core_kernel_classes_Property
     */
    public function getNameProperty();

    /**
     * Get the property related to detected client version
     *
     * @return \core_kernel_classes_Property
     */
    public function getVersionProperty();
}