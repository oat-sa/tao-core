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
 * Class ExportedTable
 * @package oat\tao\model\export\implementation\sql
 */
class ExportedTable
{
    /**
     * @var ExportedColumn[] $columns
     */
    private $columns = [];

    /**
     * @var array
     */
    private $rows = [];

    /**
     * @var string
     */
    private $tableName;

    /**
     * ExportedTable constructor.
     *
     * @param array $rows
     * @param array $typeMapping
     * @param string $tableName
     */
    public function __construct(array $rows, array $typeMapping, string $tableName)
    {
        $this->tableName = $tableName;

        foreach ($rows as $row) {

            $rowFields = [];
            foreach ($row as $key => $value) {
                $column = $this->getColumn($key);
                if (!$column) {
                    $columnType = isset($typeMapping[$key]) ? $typeMapping[$key] : ExportedColumn::TYPE_VARCHAR;
                    $column = new ExportedColumn($key, $columnType);
                    $this->addColumn($column);
                }
                $rowFields[] = new ExportedField($column, $value);
            }

            $this->addRow($rowFields);
        }
    }

    /**
     * @return ExportedColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param ExportedColumn $column
     */
    private function addColumn(ExportedColumn $column)
    {
        $this->columns[] = $column;
    }

    /**
     * @param ExportedField[] $row
     */
    private function addRow(array $row)
    {
        $this->rows[] = $row;
    }

    private function getColumn($name)
    {
        foreach ($this->columns as $column) {
            if ($column->getName() === $name) {
                return $column;
            }
        }

        return null;
    }
}
