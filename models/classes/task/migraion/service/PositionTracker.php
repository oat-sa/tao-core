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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\tao\model\task\migration\service;

use common_persistence_KeyValuePersistence;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;

class PositionTracker extends ConfigurableService
{
    private const CACHE_KEY = '::_last_known';

    public function getLastPosition(string $id): int
    {
        $start = $this->getStorage()->get($id . self::CACHE_KEY);
        return $start ? (int)$start : 0;
    }

    public function keepCurrentPosition(string $id, int $position): void
    {
        $persistence = $this->getStorage();
        $persistence->set($id . self::CACHE_KEY, $position);
    }

    private function getStorage(): common_persistence_KeyValuePersistence
    {
        return $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID)->getPersistenceById('default_kv');
    }
}
