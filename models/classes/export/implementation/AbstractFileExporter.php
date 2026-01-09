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
use Psr\Http\Message\ResponseInterface;

/**
 * @package oat\tao\model\export
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
abstract class AbstractFileExporter implements Exporter
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
     * @param array $data Data to be exported
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
     * @deprecated implement PsrResponseExporter to get Psr Response
     * @see CsvExporter
     * @param string $data Data to be exported
     * @param string|null $fileName
     */
    protected function download($data, $fileName = null)
    {
        $response = $this->preparePsrResponse(new \GuzzleHttp\Psr7\Response(), $data, $fileName);
        $this->legacyEmitResponse($response);
    }

    /**
     * @param ResponseInterface $response
     * @param string $data Data to be exported
     * @param string|null $fileName if null timestamp will be used as file name
     * @return ResponseInterface
     */
    protected function preparePsrResponse(ResponseInterface $response, $data, $fileName = null)
    {
        if ($fileName === null) {
            $fileName = (string) time();
        }

        return $response
            ->withHeader('Content-Type', $this->contentType)
            ->withHeader('Content-Disposition', 'attachment; fileName="' . $fileName . '"')
            ->withHeader('Content-Length', strlen($data))
            ->withBody(\GuzzleHttp\Psr7\Utils::streamFor($data));
    }

    /**
     * Implements old logic which writes headers and data directly to output
     * @deprecated Responses should be emitted in a centralized way using ResponseEmitter
     * @param ResponseInterface $response
     */
    private function legacyEmitResponse(ResponseInterface $response)
    {
        $this->flushOutputBuffer();
        $this->emitHeaders($response);
        $this->emitBody($response);
    }

    /**
     * @deprecated
     */
    private function flushOutputBuffer()
    {
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
    }

    /**
     * @deprecated
     * @param ResponseInterface $response
     */
    private function emitHeaders(ResponseInterface $response)
    {
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value");
            }
        }
    }

    /**
     * @deprecated
     * @param ResponseInterface $response
     */
    private function emitBody(ResponseInterface $response)
    {
        $stream = $response->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }
    }
}
