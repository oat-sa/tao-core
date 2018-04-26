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

use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;

class ArrayImportValueMapper extends ConfigurableService implements ImportValueMapperInterface
{
    use LoggerAwareTrait;

    const OPTION_DELIMITER = 'delimiter';

    const OPTION_VALUE_MAPPER = 'valueMapper';

    /**
     * @inheritdoc
     */
    public function map($value)
    {
        $mapValues = [];
        $values = explode($this->getOption(static::OPTION_DELIMITER), $value);

        foreach ($values as $value) {
            $valueMapper = $this->getOption(static::OPTION_VALUE_MAPPER);
            if ($valueMapper instanceof ImportValueMapperInterface) {
                try {
                    $mapValues[] = $valueMapper->map($value);
                } catch (RdsResourceNotFoundException $e) {
                    $this->logError($e->getMessage());
                }
            }else{
                $mapValues[] = $value;
            }
        }

        return $mapValues;
    }
}