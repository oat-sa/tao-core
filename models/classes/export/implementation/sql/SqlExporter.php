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

use Exception;
use GuzzleHttp\Psr7\Response;
use oat\tao\model\export\implementation\AbstractFileExporter;
use oat\tao\model\export\PsrResponseExporter;
use oat\taoOutcomeUi\model\table\VariableColumn;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SqlExporter
 * @package oat\tao\model\export
 */
class SqlExporter extends AbstractFileExporter implements PsrResponseExporter
{
    const FILE_NAME = 'export.sql';
    const SQL_CONTENT_TYPE = 'text/sql; charset=UTF-8';

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
     * @var string value of `Content-Type` header
     */
    protected $contentType = self::SQL_CONTENT_TYPE;

    /**
     * @param bool $columnNames
     * @param bool $download
     * @return string
     */
    public function export($columnNames = false, $download = false)
    {
        foreach ($this->columnsData as $columnData) {
            if ($columnData instanceof VariableColumn) {
                $this->mappingFieldsTypes[$columnData->label] = $this->mappingVarTypes[$columnData->baseType];
            }
        }

        $exportedTable = new ExportedTable($this->data, $this->mappingVarTypes, 'result_table');
        $sqlCreator = new SqlCreator($exportedTable);

        return $sqlCreator->getExportSql();
    }

    /**
     * Returns Psr Response with exported data and proper headers for file download
     * You can use obtained response to pass it to $this->setResponse() in controller or
     * emit directly using ResponseEmitter (for special cases)
     * @param ResponseInterface|null $originResponse
     * @return ResponseInterface
     * @throws Exception
     */
    public function getFileExportResponse(
        ResponseInterface $originResponse = null
    )
    {
        if ($originResponse === null) {
            $originResponse = new Response();
        }
        $exportedString = $this->export();
        return $this->preparePsrResponse($originResponse, $exportedString, self::FILE_NAME);
    }
}
