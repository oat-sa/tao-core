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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\export\implementation;

use GuzzleHttp\Psr7\Response;
use oat\tao\model\export\PsrResponseExporter;
use Psr\Http\Message\ResponseInterface;
use SPLTempFileObject;
use Traversable;

/**
 * Class CsvExporter
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package oat\tao\model\export
 */
class CsvExporter extends AbstractFileExporter implements PsrResponseExporter
{
    const FILE_NAME = 'export.csv';
    const CSV_CONTENT_TYPE = 'text/csv; charset=UTF-8';

    /**
     * @var string value of `Content-Type` header
     */
    protected $contentType = self::CSV_CONTENT_TYPE;

    /**
     * @param boolean $columnNames array keys will be used in the first line of CSV data as column names.
     * @param boolean $download Deprecated: use getFileExportResponse() and setResponse() in controller
     * @param string $delimiter sets the field delimiter (one character only).
     * @param string $enclosure sets the field enclosure (one character only).
     * @return string|null
     * @throws \common_exception_InvalidArgumentType
     */
    public function export($columnNames = false, $download = false, $delimiter = ',', $enclosure = '"')
    {
        $data = $this->data;

        if (!is_array($data) && !$data instanceof Traversable) {
             throw new \common_exception_InvalidArgumentType('Entity you trying to export is not Traversable');
        }

        if ($columnNames && $data) {
            array_unshift($data, array_keys($data[0]));
        }
        $file = new SPLTempFileObject();
        foreach ($data as $row) {
            $file->fputcsv($row, $delimiter, $enclosure);
        }

        $file->rewind();
        $exportData = '';
        while (!$file->eof()) {
            $exportData .= $file->fgets();
        }
        $exportData = trim($exportData);

        if ($download) {
            $this->download($exportData, self::FILE_NAME);
            return null;
        }

        return $exportData;
    }

    /**
     * Returns Psr Response with exported data and proper headers for file download
     * You can use obtained response to pass it to $this->setResponse() in controller or
     * emit directly using ResponseEmitter (for special cases)
     *
     * @param ResponseInterface|null $originResponse
     * @param boolean $columnNames array keys will be used in the first line of CSV data as column names.
     * @param string $delimiter sets the field delimiter (one character only).
     * @param string $enclosure sets the field enclosure (one character only).
     * @return ResponseInterface
     * @throws \common_exception_InvalidArgumentType
     */
    public function getFileExportResponse(
        ResponseInterface $originResponse = null,
        $columnNames = false,
        $delimiter = ',',
        $enclosure = '"'
    ) {
        if ($originResponse === null) {
            $originResponse = new Response();
        }
        $exportedString = $this->export($columnNames, false, $delimiter, $enclosure);
        return $this->preparePsrResponse($originResponse, $exportedString, self::FILE_NAME);
    }
}
