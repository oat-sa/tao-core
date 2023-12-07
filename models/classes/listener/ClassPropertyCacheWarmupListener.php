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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\listener;

use oat\generis\Helper\PropertyCache;
use oat\generis\model\data\event\CacheWarmupEvent;
use oat\generis\model\data\Ontology;
use oat\oatbox\reporting\Report;

class ClassPropertyCacheWarmupListener
{
    public function __construct(Ontology $model)
    {
        $this->model = $model;
    }

    public function handleEvent(CacheWarmupEvent $event): void
    {
        $class = new \core_kernel_classes_Class('http://www.w3.org/1999/02/22-rdf-syntax-ns#Property');
        $properties = $class->getInstances(true);
        $properties = array_unique(array_keys($properties));

        PropertyCache::warmupCachedValuesByProperties($properties);

        $event->addReport(Report::createInfo('Generated property cache.'));
    }
}
