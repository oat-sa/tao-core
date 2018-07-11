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

namespace oat\tao\model\import\service;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidService;
use oat\oatbox\service\exception\InvalidServiceManagerException;

class ImporterFactory extends ConfigurableService implements ImporterFactoryInterface
{
    /**
     * Create an importer
     *
     * User type is defined in a config mapper and is associated to a role
     *
     * @param $type
     * @return ImportServiceInterface
     * @throws \common_exception_NotFound
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function create($type)
    {
        $typeOptions    = $this->getOption(self::OPTION_MAPPERS);
        $typeOption     = isset($typeOptions[$type]) ? $typeOptions[$type] : $this->throwException();
        $importerString = isset($typeOption[self::OPTION_MAPPERS_IMPORTER]) ? $typeOption[self::OPTION_MAPPERS_IMPORTER] : $this->throwException();
        $importer       = $this->buildService($importerString, ImportServiceInterface::class);

        if (isset($typeOption[self::OPTION_MAPPERS_MAPPER])) {
            $mapperString = $typeOption[self::OPTION_MAPPERS_MAPPER];
            $mapper       = $this->buildService($mapperString);
        } else {
            $mapper = $this->getDefaultMapper();
        }

        $this->propagate($mapper);
        $importer->setMapper($mapper);

        return $importer;
    }

    /**
     * @throws \common_exception_NotFound
     */
    protected function throwException()
    {
        throw new \common_exception_NotFound('Unable to load importer for type.');
    }

    /**
     * @return ImportMapperInterface
     */
    protected function getDefaultMapper()
    {
        return new OntologyMapper([
            ImportMapperInterface::OPTION_SCHEMA => $this->getOption(self::OPTION_DEFAULT_SCHEMA)
        ]);
    }
}