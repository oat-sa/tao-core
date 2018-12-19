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

use oat\generis\model\OntologyAwareTrait;
use Sinergi\BrowserDetector\Os;

class OSDetector extends AbstractDetector
{
    use OntologyAwareTrait;

    const MAKE_CLASS = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#OSMake';

    const PROPERTY_NAME = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#OSName';
    const PROPERTY_VERSION = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#OSVersion';

    /**
     * Get Text interpretation of detected OS name
     *
     * @return string
     */
    public function getClientName()
    {
        return (new Os())->getName();
    }

    /**
     * Get version of detected OS
     *
     * @return string
     */
    public function getClientVersion()
    {
        return (new Os())->getVersion();
    }

    /**
     * Get the parent class of OS detector
     *
     * @return \core_kernel_classes_Class
     */
    public function getMakeClass()
    {
        return $this->getClass(self::MAKE_CLASS);
    }

    /**
     * Get the property related to detected OS name
     *
     * @return \core_kernel_classes_Property
     */
    public function getNameProperty()
    {
        return $this->getProperty(self::PROPERTY_NAME);
    }

    /**
     * Get the property related to detected OS version
     *
     * @return \core_kernel_classes_Property
     */
    public function getVersionProperty()
    {
        return $this->getProperty(self::PROPERTY_VERSION);
    }


}