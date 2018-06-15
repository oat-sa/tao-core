<?php
/**
 *
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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\model\metrics\implementations;


use oat\oatbox\service\ConfigurableService;

abstract class abstractMetrics extends ConfigurableService
{
    const OPTION_PERSISTENCE = 'persistence';
    const OPTION_TTL = 'ttl';

    /**
     * @return \common_persistence_KeyValuePersistence
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    protected function getPersistence()
    {
        $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
        return $this->getServiceManager()->get(\common_persistence_Manager::class)->getPersistenceById($persistenceId);
    }

    /**
     * Collect values, caches
     * @param bool $force
     * @return mixed
     */
    abstract public function collect($force = false);
}