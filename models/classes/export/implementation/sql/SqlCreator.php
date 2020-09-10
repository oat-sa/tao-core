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

        return $sqlCreateTable . PHP_EOL . PHP_EOL . $sqlInsert;
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

        return sprintf("CREATE TABLE IF NOT EXISTS %s (%s\t%s%s);",
            $this->table->getTableName(),
            PHP_EOL,
            implode("," . PHP_EOL . "\t", $columnsCreatingStringArray),
            PHP_EOL
        );
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

            $rowValuesString = implode("," . PHP_EOL . "\t   ", $rowValuesArray);
            $fieldInsertArray[] = "(" . PHP_EOL . "\t   $rowValuesString" . PHP_EOL . "\t)";
        }

        $columnNamesString = implode("," . PHP_EOL . "\t   ", $columnNamesArray);
        $fieldInsertString = implode("," . PHP_EOL . "\t", $fieldInsertArray);

        return sprintf("INSERT INTO %s (%s\t   %s%s) VALUES %s;",
            $this->table->getTableName(),
            PHP_EOL,
            $columnNamesString,
            PHP_EOL,
            $fieldInsertString
        );
    }
}
