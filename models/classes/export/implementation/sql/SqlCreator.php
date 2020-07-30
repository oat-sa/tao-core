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
 * Class SqlCreator
 * @package oat\tao\model\export\implementation\sql
 */
class SqlCreator
{
    /**
     * @var ExportedTable
     */
    private $table;

    public function __construct(ExportedTable $table)
    {
        $this->table = $table;
    }

    /**
     * @return string
     */
    public function getExportSql() : string
    {
        $sqlCreateTable = $this->getCreateTableSql();
        $sqlInsert = $this->getInsertSql();

        return "$sqlCreateTable\n\n$sqlInsert";
    }

    /**
     * @return string
     */
    private function getCreateTableSql(): string
    {
        $columnsCreatingStringArray = [];

        foreach ($this->table->getColumns() as $column) {
            $columnsCreatingStringArray[] = $column->getColumnCreatingString();
        }

        return sprintf("CREATE TABLE IF NOT EXISTS %s (\n\t%s\n);", $this->table->getTableName(), implode(",\n\t", $columnsCreatingStringArray));
    }

    /**
     * @return string
     */
    private function getInsertSql() : string
    {
        $columnNamesArray = [];
        foreach ($this->table->getColumns() as $column)
        {
            $columnNamesArray[] = $column->getFormattedName();
        }

        $fieldInsertArray = [];
        foreach ($this->table->getRows() as $row) {

            $rowValuesArray = [];

            /**@var $field ExportedField */
            foreach ($row as $key => $field) {
                $rowValuesArray[] = $field->getFormattedValue();
            }

            $rowValuesString = implode(",\n\t   ", $rowValuesArray);
            $fieldInsertArray[] = "(\n\t   $rowValuesString\n\t)";;
        }

        $columnNamesString = implode(",\n\t   ", $columnNamesArray);
        $fieldInsertString = implode(",\n\t", $fieldInsertArray);

        return sprintf("INSERT INTO %s (\n\t   %s\n) VALUES %s;", $this->table->getTableName(), $columnNamesString, $fieldInsertString);
    }
}
