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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\user\import;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidService;
use oat\oatbox\service\exception\InvalidServiceManagerException;

class UserCsvImporterFactory extends ConfigurableService
{
    const SERVICE_ID = 'tao/userCsvImporterFactory';

    const OPTION_DEFAULT_SCHEMA = 'default-schema';
    const OPTION_MAPPERS = 'mappers';
    const OPTION_MAPPERS_IMPORTER = 'importer';
    const OPTION_MAPPERS_MAPPER = 'mapper';

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
    public function getImporter($type)
    {
        $typeOptions = $this->getOption(self::OPTION_MAPPERS);
        if (isset($typeOptions[$type])) {
            $typeOption = $typeOptions[$type];
            if (isset($typeOption[self::OPTION_MAPPERS_IMPORTER])) {
                $importer = $this->buildService($typeOption[self::OPTION_MAPPERS_IMPORTER], UserImportServiceInterface::class);
                if (isset($typeOption[self::OPTION_MAPPERS_MAPPER])) {
                    $mapper = $this->buildService($typeOption[self::OPTION_MAPPERS_MAPPER]);
                } else {
                    $mapper = new OntologyUserMapper([UserMapper::OPTION_SCHEMA => $this->getOption(self::OPTION_DEFAULT_SCHEMA)]);
                }
                $importer->setMapper($mapper);
                return $importer;
            }
        }
        throw new \common_exception_NotFound('Unable to load importer for type : ' . $type);
    }

}