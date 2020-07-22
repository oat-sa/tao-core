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
use oat\tao\model\export\PsrResponseExporter;
use oat\taoOutcomeUi\model\table\VariableColumn;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CsvExporter
 * @package oat\tao\model\export
 */
class SqlExporter extends AbstractFileExporter implements PsrResponseExporter
{
    const FILE_NAME = 'export.sql';
    const SQL_CONTENT_TYPE = 'text/sql; charset=UTF-8';

    private $mappingVarTypes = [
        'integer' => 'INT',
        'boolean' => 'BOOLEAN',
        'identifier' => 'VARCHAR(64000)',
        'duration' => 'DECIMAL',
        'float' => 'DECIMAL'
    ];

    private $mappingFieldsTypes = [
        'Test Taker ID' => 'VARCHAR(64000)',
        'Test Taker' => 'VARCHAR(64000)',
        'Login' => 'VARCHAR(64000)',
        'First Name' => 'VARCHAR(64000)',
        'Last Name' => 'VARCHAR(64000)',
        'Mail' => 'VARCHAR(64000)',
        'Interface Language' => 'VARCHAR(64000)',
        'Group' => 'VARCHAR(64000)',
        'Delivery' => 'VARCHAR(64000)',
        'Title' => 'VARCHAR(64000)',
        'Max. Executions (default: unlimited)' => 'VARCHAR(64000)',
        'Start Date' => 'TIMESTAMP',
        'End Date' => 'TIMESTAMP',
        'Display Order' => 'VARCHAR(64000)',
        'Access' => 'VARCHAR(64000)',
        'Runtime' => 'VARCHAR(64000)',
        'Delivery container serial' => 'VARCHAR(64000)',
        'Delivery origin' => 'VARCHAR(64000)',
        'Compilation Directory' => 'VARCHAR(64000)',
        'Compilation Time' => 'INT',
        'Start Delivery Execution' => 'TIMESTAMP',
        'End Delivery Execution' => 'TIMESTAMP'
    ];

    private $testLabel = '';

    /**
     * @var string value of `Content-Type` header
     */
    protected $contentType = self::SQL_CONTENT_TYPE;

    /**
     * @return string
     * @throws \Exception
     */
    public function export()
    {
        //defining test label
        foreach ($this->columnsData as $columnData) {
            if ($columnData instanceof VariableColumn) {
                if ($columnData->getIdentifier() === 'LtiOutcome') {
                    $this->testLabel = $columnData->contextLabel;
                }
            }
        }

        //defining column types
        $variableColumnTypes = [];
        foreach ($this->columnsData as $columnData) {
            if ($columnData instanceof VariableColumn) {
                $fieldName = $this->convertFieldName($columnData->getLabel());
                $variableColumnTypes[$fieldName] = $this->mappingVarTypes[$columnData->baseType];
            }
        }

        $testTakerColumnTypes = [];
        foreach ($this->data[0] as $key => $value) {
            $fieldName = $this->convertFieldName($key);
            if (isset($this->mappingFieldsTypes[$key])) {
                $testTakerColumnTypes[$fieldName] = $this->mappingFieldsTypes[$key];
            }
        }
        $columnTypes = array_merge($testTakerColumnTypes, $variableColumnTypes);


        //create INSERT

        $titlesInsertArray = [];
        foreach ($this->data[0] as $key => $value) {
            $titlesInsertArray [] = $this->convertFieldName($key);
        }
        $titlesInsert = implode(",\n\t   ", $titlesInsertArray);

        $bodyInsertArray = [];
        foreach ($this->data as $line) {

            foreach ($line as $key => $value) {
                $value = addslashes($value);
                $fieldName = $this->convertFieldName($key);
                // if there are several values in the field, override the type to VARCHAR
                if (strpos($value, '|') !== false) {
                    $columnTypes[$fieldName] = 'VARCHAR(64000)';
                }
                if (is_null($value) || ($value == '' && $columnTypes[$fieldName] != 'VARCHAR(64000)')) {
                    $fields[$fieldName] = 'null';
                } elseif ($columnTypes[$fieldName] == 'INT' || $columnTypes[$fieldName] == 'BOOLEAN') {
                    $fields[$fieldName] = "$value";
                } elseif($columnTypes[$fieldName] == 'TIMESTAMP') {
                    $date = (\DateTime::createFromFormat('d/m/Y H:i:s', $value))->format('Y-m-d H:i:s');
                    $fields[$fieldName] = "'$date'";
                } else {
                    $fields[$fieldName] = "'$value'";
                }
            }

            $values = implode(",\n\t   ", $fields);
            $bodyInsertArray [] = "(\n\t   $values\n\t)";
        }
        $bodyInsert = implode(",\n\t", $bodyInsertArray);

        $insertSql = "INSERT INTO tests_result (\n\t   $titlesInsert\n) VALUES $bodyInsert;";

        //create table
        $createTableColumns = [];
        foreach ($this->data[0] as $key => $value) {
            $fieldName = $this->convertFieldName($key);
            $createTableColumns[] = $fieldName . " " . $columnTypes[$fieldName];
        }
        $sqlCreateTable = sprintf("CREATE TABLE tests_result (\n\t%s\n);", implode(",\n\t", $createTableColumns));

        return "$sqlCreateTable\n\n$insertSql";
    }

    /**
     * @param $fieldName
     * @return string|string[]
     */
    public function convertFieldName($fieldName)
    {
        //iif column starts with the test label, replace test label with the word 'test'
        if ($this->testLabel && strpos($fieldName, $this->testLabel) !== false ) {
            $fieldName = str_replace($this->testLabel, 'test', $fieldName);
        }

        //if the title starts with a number, must be done for other symbols
        if ((int)substr($fieldName, 0, 1) > 0 || substr($fieldName, 0, 1) == '0') {
            $fieldName = "_$fieldName";
        }

        //reserved sql word
        if ($fieldName == 'Group') {
            $fieldName = '_group';
        }

        return addslashes(str_replace([' ', '-', '.', '(', ')', ':'], '_', mb_strtolower($fieldName)));
    }

    /**
     * Returns Psr Response with exported data and proper headers for file download
     * You can use obtained response to pass it to $this->setResponse() in controller or
     * emit directly using ResponseEmitter (for special cases)
     * @param ResponseInterface|null $originResponse
     * @return ResponseInterface
     * @throws \Exception
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
