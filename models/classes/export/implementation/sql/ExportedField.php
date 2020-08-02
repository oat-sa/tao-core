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
 * Class ExportedField
 * @package oat\tao\model\export\implementation\sql
 */
class ExportedField
{
    /**
     * @var ExportedColumn $column
     */
    private $column;

    /**
     * @var mixed $value
     */
    private $value;

    public function __construct(ExportedColumn $column, $value)
    {
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * @return ExportedColumn
     */
    public function getColumn(): ExportedColumn
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getFormattedValue() : string
    {
        if (is_null($this->value) || ($this->value === '' && $this->getColumn()->getType() !== ExportedColumn::TYPE_VARCHAR)) {
            return 'null';
        }

        $value = addslashes($this->value);

        switch ($this->getColumn()->getType()) {
            case ExportedColumn::TYPE_BOOLEAN:
            case ExportedColumn::TYPE_INTEGER:
                return "$value";
            case ExportedColumn::TYPE_TIMESTAMP:
                $date = (\DateTime::createFromFormat('d/m/Y H:i:s', $value))->format('Y-m-d H:i:s');
                return "'$date'";
            case ExportedColumn::TYPE_VARCHAR:
            case ExportedColumn::TYPE_DECIMAL:
                return "'$value'";
        }
    }
}
