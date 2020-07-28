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
 *
 */

namespace oat\tao\model\export\implementation\sql;

/**
 * Class ExportedColumn
 * @package oat\tao\model\export\implementation\sql
 */
class ExportedColumn
{
    const TYPE_BOOLEAN = 'BOOLEAN';
    const TYPE_INTEGER = 'INT';
    const TYPE_DECIMAL = 'DECIMAL';
    const TYPE_VARCHAR = 'VARCHAR(16000)';
    const TYPE_TIMESTAMP = 'TIMESTAMP';

    const PREFIX = 'col_';

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $type
     */
    private $type;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFormattedName(): string
    {
        return self::PREFIX . $this->convertColumnName($this->name);
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getColumnCreatingString(): string
    {
        return $this->getFormattedName() . ' ' . $this->getType();
    }

    /**
     * @param $columnName
     * @return string
     */
    private function convertColumnName($columnName) : string
    {
        return preg_replace(['/(\s+|-)/', '/[^A-Za-z0-9_]/'], ['_', ''], strtolower($columnName));
    }
}
