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
 */

declare(strict_types=1);

use oat\generis\test\TestCase;
use oat\tao\model\http\ContentDetector;

use Psr\Http\Message\StreamInterface;

use function GuzzleHttp\Psr7\stream_for;

class ContentDetectorTest extends TestCase
{

    private const ENCODED_IMAGE = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEX/TQBcNTh/AAAAAXRSTlPM0jRW/QAAAApJREFUeJxjYgAAAAYAAzY3fKgAAAAASUVORK5CYII=';

    /**
     * @var ContentDetector
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ContentDetector();
        parent::setUp();
    }

    public function testIsGzip(): void
    {
        $this->assertFalse($this->subject->isGzip(stream_for('string')));
        $this->assertFalse($this->subject->isGzip($this->getFileStream(__FILE__)));
        $this->assertFalse($this->subject->isGzip(stream_for(base64_decode(self::ENCODED_IMAGE))));
        $this->assertTrue($this->subject->isGzip(stream_for(gzencode('string'))));
    }

    public function testIsGzipableMime(): void
    {
        $this->assertFalse($this->subject->isGzipableMime('text/html'));
        $this->assertTrue($this->subject->isGzipableMime(tao_helpers_File::MIME_SVG));
    }

    private function getFileStream($path): StreamInterface
    {
        return stream_for(fopen($path, 'rb'));
    }
}
