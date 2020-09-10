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

namespace oat\tao\model\export\implementation;

use GuzzleHttp\Psr7\Response;
use oat\tao\model\export\implementation\sql\ExportedTable;
use oat\tao\model\export\implementation\sql\SqlCreator;
use oat\tao\model\export\PsrResponseExporter;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SqlExporter
 * @package oat\tao\model\export\implementation
 */
class SqlExporter extends AbstractFileExporter implements PsrResponseExporter
{
    private $tableName;
    /**
     * @var array
     */
    private $typesMapping;

    /**
     * @param array $data Data to be exported
     * @param array $typesMapping
     * @param string $tableName
     */
    public function __construct($data, array $typesMapping, string $tableName = 'result_table')
    {
        parent::__construct($data);
        $this->data = $data;
        $this->typesMapping = $typesMapping;
        $this->tableName = $tableName;
    }

    /**
     * @return ResponseInterface|string
     */
    public function export()
    {
        $exportedTable = new ExportedTable($this->data, $this->typesMapping, $this->tableName);
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
