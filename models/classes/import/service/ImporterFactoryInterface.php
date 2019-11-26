<?php

declare(strict_types=1);

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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\import\service;

use oat\oatbox\service\exception\InvalidService;
use oat\oatbox\service\exception\InvalidServiceManagerException;

interface ImporterFactoryInterface
{
    public const OPTION_DEFAULT_SCHEMA = 'default-schema';

    public const OPTION_MAPPERS = 'mappers';

    public const OPTION_MAPPERS_IMPORTER = 'importer';

    public const OPTION_MAPPERS_MAPPER = 'mapper';

    /**
     * Create an importer for the given user type.
     *
     * User type is defined in a config mapper and is associated to a role
     *
     * @param $type
     * @return mixed
     * @throws \common_exception_NotFound
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function create($type);
}
