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

use oat\tao\model\import\service\ImporterFactory;
use oat\tao\model\import\service\ImportMapperInterface;

class UserCsvImporterFactory extends ImporterFactory
{
    const SERVICE_ID = 'tao/userCsvImporterFactory';

    /**
     * @return ImportMapperInterface
     */
    protected function getDefaultMapper()
    {
        return new OntologyUserMapper([UserMapperInterface::OPTION_SCHEMA => $this->getOption(self::OPTION_DEFAULT_SCHEMA)]);
    }
}