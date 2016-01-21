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

use oat\tao\model\export\Exporter;

/**
 * Class AbstractExporter
 * @package oat\tao\model\export
 */
abstract class AbstractExporter implements Exporter
{
    /**
     * @var string value of `Content-Type` header
     */
    protected $contentType = 'text/plain; charset=UTF-8';

    /**
     * @var mixed Data to be exported
     */
    protected $data;

    /**
     * AbstractExporter constructor.
     * @param $data Data to be exported
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Export data as string
     * @return string
     */
    abstract public function export();

    /**
     * Send exported data to end user
     * @param string $fileName
     * @return mixed
     */
    public function download($fileName = null)
    {
        $exportData = $this->export();

        if ($fileName === null) {
            $fileName = time();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        header('Content-Type: ' . $this->contentType);
        header('Content-Disposition: attachment; fileName="' . $fileName .'"');
        header("Content-Length: " . strlen($exportData));

        echo $exportData;
    }
}