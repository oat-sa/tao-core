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

use GuzzleHttp\Psr7\Response;
use oat\tao\model\export\implementation\AbstractFileExporter;
use oat\tao\model\export\PsrResponseExporter;
use oat\taoOutcomeUi\model\table\VariableColumn;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SqlExporter
 * @package oat\tao\model\export\implementation\sql
 */
class SqlExporter extends AbstractFileExporter implements PsrResponseExporter
{
    private $mappingVarTypes = [
        'integer'    => ExportedColumn::TYPE_INTEGER,
        'boolean'    => ExportedColumn::TYPE_BOOLEAN,
        'identifier' => ExportedColumn::TYPE_VARCHAR,
        'duration'   => ExportedColumn::TYPE_DECIMAL,
        'float'      => ExportedColumn::TYPE_DECIMAL
    ];

    private $mappingFieldsTypes = [
        'Test Taker ID'             => ExportedColumn::TYPE_VARCHAR,
        'Test Taker'                => ExportedColumn::TYPE_VARCHAR,
        'Login'                     => ExportedColumn::TYPE_VARCHAR,
        'First Name'                => ExportedColumn::TYPE_VARCHAR,
        'Last Name'                 => ExportedColumn::TYPE_VARCHAR,
        'Mail'                      => ExportedColumn::TYPE_VARCHAR,
        'Interface Language'        => ExportedColumn::TYPE_VARCHAR,
        'Group'                     => ExportedColumn::TYPE_VARCHAR,
        'Delivery'                  => ExportedColumn::TYPE_VARCHAR,
        'Title'                     => ExportedColumn::TYPE_VARCHAR,
        'Start Date'                => ExportedColumn::TYPE_TIMESTAMP,
        'End Date'                  => ExportedColumn::TYPE_TIMESTAMP,
        'Display Order'             => ExportedColumn::TYPE_VARCHAR,
        'Access'                    => ExportedColumn::TYPE_VARCHAR,
        'Runtime'                   => ExportedColumn::TYPE_VARCHAR,
        'Delivery container serial' => ExportedColumn::TYPE_VARCHAR,
        'Delivery origin'           => ExportedColumn::TYPE_VARCHAR,
        'Compilation Directory'     => ExportedColumn::TYPE_VARCHAR,
        'Compilation Time'          => ExportedColumn::TYPE_INTEGER,
        'Start Delivery Execution'  => ExportedColumn::TYPE_TIMESTAMP,
        'End Delivery Execution'    => ExportedColumn::TYPE_TIMESTAMP,
        'Max. Executions (default: unlimited)' => ExportedColumn::TYPE_VARCHAR,
    ];

    /**
     * @inheritdoc
     */
    public function export()
    {
        foreach ($this->columnsData as $columnData) {
            if ($columnData instanceof VariableColumn) {
                $this->mappingFieldsTypes[$columnData->getLabel()] = $this->mappingVarTypes[$columnData->getBaseType()];
            }
        }

        $exportedTable = new ExportedTable($this->data, $this->mappingVarTypes, 'result_table');
        $sqlCreator = new SqlCreator($exportedTable);

        return $sqlCreator->getExportSql();
    }

    /**
     * @inheritdoc
     */
    public function getFileExportResponse(
        ResponseInterface $originResponse = null
    )
    {
        if ($originResponse === null) {
            $originResponse = new Response();
        }

        $exportedString = $this->export();
        return $this->preparePsrResponse($originResponse, $exportedString, 'export.sql');
    }
}
