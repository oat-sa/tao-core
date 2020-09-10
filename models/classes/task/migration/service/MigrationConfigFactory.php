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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\task\migration\service;

use common_exception_MissingParameter;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\task\migration\MigrationConfig;

class MigrationConfigFactory extends ConfigurableService implements MigrationConfigFactoryInterface
{
    public function create(array $parameters): MigrationConfig
    {
        if (
            !array_key_exists('start', $parameters) ||
            !array_key_exists('chunkSize', $parameters) ||
            !array_key_exists('pickSize', $parameters) ||
            !array_key_exists('repeat', $parameters)
        ) {
            throw new common_exception_MissingParameter();
        }

        return new MigrationConfig(
            ['start' => $parameters['start']],
            (int)$parameters['chunkSize'],
            (int)$parameters['pickSize'],
            (bool)$parameters['repeat']
        );
    }
}
