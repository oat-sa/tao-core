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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\resources\listeners;

use oat\generis\model\data\event\ResourceCreated;
use oat\generis\model\data\event\ResourceUpdated;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\TaoOntology;

/**
 * Class ResourceListeners
 * @package oat\tao\model\resources\listeners
 */
class ResourceListeners
{
    /**
     * @param ResourceCreated $event
     * @return \common_report_Report
     */
    public static function createdResourceEvent(ResourceCreated $event)
    {
        /** @var \core_kernel_classes_Resource $resource */
        $resource = $event->getResource();
        $property = new \core_kernel_classes_Property(TaoOntology::PROPERTY_UPDATED_AT);
        $resource->editPropertyValues($property, time());
        $report = \common_report_Report::createSuccess();
        return $report;

    }

    /**
     * @param ResourceUpdated $event
     * @return \common_report_Report
     * @throws \core_kernel_persistence_Exception
     */
    public static function updatedResourceEvent(ResourceUpdated $event)
    {
        $resource = $event->getResource();
        $property = new \core_kernel_classes_Property(TaoOntology::PROPERTY_UPDATED_AT);
        $updatedAt = $resource->getOnePropertyValue($property);

        if ($updatedAt && $updatedAt instanceof \core_kernel_classes_Literal) {
            $updatedAt = (integer) $updatedAt->literal;
        }

        $now = time();
        if ($updatedAt && ($now - $updatedAt) > 1) {
            $resource->editPropertyValues($property, time());
        }
        $report = \common_report_Report::createSuccess();
        return $report;

    }
}
