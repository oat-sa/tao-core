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
 */

namespace oat\tao\model\mvc;

use OAT\Library\CorrelationIds\Builder\CorrelationIdsRegistryBuilder;
use OAT\Library\CorrelationIds\Generator\CorrelationIdGenerator;
use OAT\Library\CorrelationIds\Registry\CorrelationIdsRegistryInterface;
use oat\oatbox\service\ConfigurableService;

class CorrelationIdsService extends ConfigurableService
{
    /** @var CorrelationIdsRegistryInterface */
    private $registry;

    /**
     * Builds a correlation ids registry.
     * @param array $headers optional current request headers ([] if invoked from CLI)
     * @return CorrelationIdsRegistryInterface
     */
    public function getRegistry(array $headers): CorrelationIdsRegistryInterface
    {
        if ($this->registry === null) {
            $correlationIdsRegistryBuilder = new CorrelationIdsRegistryBuilder(new CorrelationIdGenerator());
            $this->registry = $correlationIdsRegistryBuilder->buildFromRequestHeaders($headers);
        }
        return $this->registry;
    }
}
