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

use oat\generis\model\OntologyRdfs;
use oat\oatbox\Configurable;

abstract class AbstractDetector extends Configurable implements DetectorInterface
{
    /**
     * Get the parent class of client
     *
     * @return \core_kernel_classes_Class
     */
    abstract protected function getMakeClass();

    /**
     * Get the resource associated to the detected client
     *
     * @return \core_kernel_classes_Resource|null
     */
    public function getClientNameResource()
    {
        $detectedName = $this->getClientName();

        $results = $this->getMakeClass()->searchInstances(
            [ OntologyRdfs::RDFS_LABEL => $detectedName ],
            [ 'like' => false ]
        );

        $result = array_pop($results);

        return $result;
    }
}