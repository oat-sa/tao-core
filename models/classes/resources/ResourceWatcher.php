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
 * Copyright (c) 2017-2021 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\resources;

use core_kernel_classes_Resource;
use oat\generis\model\data\event\ResourceCreated;
use oat\generis\model\data\event\ResourceDeleted;
use oat\generis\model\data\event\ResourceUpdated;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\TaoOntology;

/**
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class ResourceWatcher extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'tao/ResourceWatcher';

    /** Time in seconds for updatedAt threshold */
    const OPTION_THRESHOLD = 'threshold';

    /** @var array */
    protected $updatedAtCache = [];

    public function catchCreatedResourceEvent(ResourceCreated $event): void
    {
        $resource = $event->getResource();
        $property = $this->getProperty(TaoOntology::PROPERTY_UPDATED_AT);
        $now = microtime(true);
        $this->updatedAtCache = [];
        $this->updatedAtCache[$resource->getUri()] = $now;
        $resource->editPropertyValues($property, $now);

        $this->getLogger()->debug('triggering index update on resourceCreated event');
    }

    /**
     * @throws \core_kernel_persistence_Exception
     */
    public function catchUpdatedResourceEvent(ResourceUpdated $event): void
    {
        $resource = $event->getResource();
        $updatedAt = $this->getUpdatedAt($resource);
        if ($updatedAt && $updatedAt instanceof \core_kernel_classes_Literal) {
            $updatedAt = (int) $updatedAt->literal;
        }

        $now = microtime(true);
        $threshold = $this->getOption(self::OPTION_THRESHOLD);

        if ($updatedAt === null || ($now - $updatedAt) > $threshold) {
            $this->getLogger()->debug('triggering index update on resourceUpdated event');

            $property = $this->getProperty(TaoOntology::PROPERTY_UPDATED_AT);
            $this->updatedAtCache[$resource->getUri()] = $now;
            $resource->editPropertyValues($property, $now);
        }
    }

    public function catchDeletedResourceEvent(ResourceDeleted $event): void
    {
        $this->getLogger()->debug('Method' . __METHOD__ . ' shall not be called anymore');
    }

     /**
     * @return \core_kernel_classes_Container
     * @throws \core_kernel_persistence_Exception
     */
    public function getUpdatedAt(core_kernel_classes_Resource $resource)
    {
        if (isset($this->updatedAtCache[$resource->getUri()])) {
            return $this->updatedAtCache[$resource->getUri()];
        }

        $property = $this->getProperty(TaoOntology::PROPERTY_UPDATED_AT);
        $updatedAt = $resource->getOnePropertyValue($property);

        if ($updatedAt && $updatedAt instanceof \core_kernel_classes_Literal) {
            $updatedAt = (int) $updatedAt->literal;
        }

        $this->updatedAtCache[$resource->getUri()] = $updatedAt;

        return $updatedAt;
    }
}
