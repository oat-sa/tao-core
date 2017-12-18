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

namespace oat\tao\model\resources;

use oat\generis\model\data\event\ResourceCreated;
use oat\generis\model\data\event\ResourceUpdated;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\TaoOntology;

/**
 * Class ResourceUpdater
 * @package oat\tao\model\resources
 */
class ResourceUpdater extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'tao/ResourceUpdater';

    protected $updatedAtCache;

    /**
     * @param ResourceCreated $event
     * @return \common_report_Report
     */
    public function catchCreatedResourceEvent(ResourceCreated $event)
    {
        /** @var \core_kernel_classes_Resource $resource */
        $resource = $event->getResource();
        $property = $this->getProperty(TaoOntology::PROPERTY_UPDATED_AT);
        $now = time();
        $this->updatedAtCache[$resource->getUri()] = $now;
        $resource->editPropertyValues($property, $now);
        $report = \common_report_Report::createSuccess();
        return $report;

    }

    /**
     * @param ResourceUpdated $event
     * @return \common_report_Report
     * @throws \core_kernel_persistence_Exception
     */
    public function catchUpdatedResourceEvent(ResourceUpdated $event)
    {
        $resource = $event->getResource();
        $updatedAt = $this->getUpdatedAt($resource);

        if ($updatedAt && $updatedAt instanceof \core_kernel_classes_Literal) {
            $updatedAt = (integer) $updatedAt->literal;
        }
        $now = time();
        if ($updatedAt && ($now - $updatedAt) > 1) {
            $property = $this->getProperty(TaoOntology::PROPERTY_UPDATED_AT);
            $this->updatedAtCache[$resource->getUri()] = $now;
            $resource->editPropertyValues($property, $now);
        }
        $report = \common_report_Report::createSuccess();
        return $report;

    }

     /**
     * @param \core_kernel_classes_Resource $resource
     * @return \core_kernel_classes_Container
     * @throws \core_kernel_persistence_Exception
     */
    public function getUpdatedAt(\core_kernel_classes_Resource $resource)
    {
        $property = $this->getProperty(TaoOntology::PROPERTY_UPDATED_AT);
        $now = time();
        if (isset($this->updatedAtCache[$resource->getUri()]) && ($now - $this->updatedAtCache[$resource->getUri()]) < 1) {
            $updatedAt = $this->updatedAtCache[$resource->getUri()];

        } else {
            $updatedAt = $resource->getOnePropertyValue($property);
            if ($updatedAt && $updatedAt instanceof \core_kernel_classes_Literal) {
                $updatedAt = (integer) $updatedAt->literal;
            }
            $this->updatedAtCache[$resource->getUri()] = $updatedAt;
        }
        return $updatedAt;
    }
}
