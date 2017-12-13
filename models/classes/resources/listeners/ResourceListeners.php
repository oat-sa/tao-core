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
use oat\tao\model\resources\events\ResourceUpdatedAt;
use oat\tao\model\TaoOntology;

class ResourceListeners
{
    public static function createdResourceEvent(ResourceCreated $event)
    {
        /** @var \core_kernel_classes_Resource $resource */
        $resource = new \core_kernel_classes_Resource($event->getResource());
        $property = new \core_kernel_classes_Property(TaoOntology::PROPERTY_UPDATED_AT);
        $resource->editPropertyValues($property, time());
        $report = \common_report_Report::createSuccess();
        return $report;

    }

    public static function updatedResourceEvent(ResourceUpdated $event)
    {
        $resource = new \core_kernel_classes_Resource($event->getResource());
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

    public static function updatedUpdatedAt(ResourceUpdatedAt $event)
    {
        $resource = new \core_kernel_classes_Resource($event->getResource());
        $property = new \core_kernel_classes_Property(TaoOntology::PROPERTY_UPDATED_AT);
        $resource->editPropertyValues($property, time());
        $report = \common_report_Report::createSuccess();
        return $report;

    }


}
