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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\stream;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class StreamRange
 * @package oat\tao\model\stream
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class StreamRange
{
    /**
     * @var integer
     */
    private $firstPos;

    /**
     * @var integer
     */
    private $lastPos;

    /**
     * StreamRange constructor.
     * @param StreamInterface $stream
     * @param string $range
     * @throws
     */
    public function __construct(StreamInterface $stream, $range)
    {
        $range = trim($range);
        $length = $stream->getSize();
        if (preg_match('/^(\d+)\-$/', $range, $match)) {
            $this->firstPos = intval($match[1]);

            if ($this->firstPos > ($length - 1)) {
                throw new StreamRangeException('HTTP/1.1 416 Requested Range Not Satisfiable');
            }
            $this->lastPos = $length - 1;

        } elseif (preg_match('/^(\d+)\-(\d+)$/', $range, $match)) {
            $this->firstPos = intval($match[1]);
            $this->lastPos = intval($match[2]);

            if ($this->lastPos < $this->firstPos || $this->lastPos > ($length - 1)) {
                throw new StreamRangeException('HTTP/1.1 416 Requested Range Not Satisfiable');
            }
        } elseif (preg_match('/^\-(\d+)$/', $range, $match)) {
            $suffixLength = intval($match[1]);

            if ($suffixLength === 0 || $suffixLength > $length) {
                throw new StreamRangeException('HTTP/1.1 416 Requested Range Not Satisfiable');
            }

            $this->firstPos = $length - $suffixLength;
            $this->lastPos = $length - 1;
        } else {
            throw new StreamRangeException('HTTP/1.1 416 Requested Range Not Satisfiable');
        }
    }

    /**
     * Create array of StreamRange instances based on current request range headers
     * @param StreamInterface $stream
     * @param ServerRequestInterface $request
     * @throws StreamRangeException
     * @return StreamRange[]
     */
    public static function createFromRequest(StreamInterface $stream, ServerRequestInterface $request = null)
    {
        $result = [];
        if ($request === null) {
            $headers = \tao_helpers_Http::getHeaders();
            $rangeHeader = isset($headers['Range']) ? [$headers['Range']] : null;
        } else {
            $rangeHeader = $request->hasHeader('Range') ? $request->getHeader('Range') : null;
        }
        if ($rangeHeader) {
            $ranges = explode(',', $rangeHeader[0]);
            foreach($ranges as $range) {
                $range = str_replace('bytes=', '', $range);
                $result[] = new StreamRange($stream, $range);
            }
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getFirstPos()
    {
        return $this->firstPos;
    }

    /**
     * @return int
     */
    public function getLastPos()
    {
        return $this->lastPos;
    }
}