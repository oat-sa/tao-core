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

use League\Csv\Writer;
use Traversable;

/**
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package oat\tao\model\export
 */
class CsvExporter
{
    /**
     * @var mixed Data to be exported
     */
    protected $data;

    /**
     * @param array $data Data to be exported
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @param boolean $columnNames array keys will be used in the first line of CSV data as column names.
     * @param boolean $download
     * @param string $delimiter sets the field delimiter (one character only).
     * @param string $enclosure sets the field enclosure (one character only).
     *
     * @return string
     *
     * @throws \League\Csv\Exception
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

        if ($download) {
            $this->sendHeaders('export.csv');
            $this->echoContent($data, $delimiter, $enclosure);
        } else {
            return $this->returnContent($data, $delimiter, $enclosure);
        }
    }

    protected function sendHeaders($fileName = null)
    {
        if ($fileName === null) {
            $fileName = (string)time();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; fileName="' . $fileName .'"');
    }

    /**
     * @param mixed $data
     * @param string $delimiter
     * @param string $enclosure
     *
     * @return string
     *
     * @throws \League\Csv\Exception
     */
    private function returnContent($data, $delimiter, $enclosure)
    {
        $csv = Writer::createFromString('');
        $csv->setDelimiter($delimiter)->setEnclosure($enclosure);
        $csv->insertAll($data);

        return $csv->getContent();
    }

    /**
     * @param mixed $data
     * @param string $delimiter
     * @param string $enclosure
     */
    private function echoContent($data, $delimiter, $enclosure)
    {
        $out = fopen('php://output', 'wb');
        if (false === $out) {
            throw new \RuntimeException('Can not open stdout for writing');
        }

        foreach ($data as $row) {
            if (!empty($row)) {
                $result = fputcsv($out, $row, $delimiter, $enclosure);
                if (false === $result) {
                    throw new \RuntimeException('Can not write to stdout');
                }
            }
        }

        fclose($out);
    }
}
