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

namespace oat\tao\test\unit\http;

use PHPUnit\Framework\TestCase;
use oat\tao\model\http\ContentDetector;
use Psr\Http\Message\StreamInterface;
use tao_helpers_File;

use GuzzleHttp\Psr7\Utils;

class ContentDetectorTest extends TestCase
{
    private const ENCODED_IMAGE = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEX/TQBcNTh/AAAAAXRSTlPM0jRW/QAA'
        . 'AApJREFUeJxjYgAAAAYAAzY3fKgAAAAASUVORK5CYII=';
    private const ENCODED_GZIP = 'H4sICGdTa18AA0ZGNEQwMC0wLjgucG5nAOsM8HPn5ZLiYmBg4PX0cAkC0owgzMgMJFVvh50CUswBPiGu/30ZY'
        . 'kwt6kFyJUF+wWcumYT9BXK4PF0cQyrmJCcB2WwMzGbmNSuALAZPVz+XdU4JTQBnUUFvXwAAAA==';

    /**
     * @var ContentDetector
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ContentDetector();
        parent::setUp();
    }

    public function testIsNotGzip(): void
    {
        $this->assertFalse($this->subject->isGzip(Utils::streamFor('string')));
        $this->assertFalse($this->subject->isGzip($this->getFileStream(__FILE__)));
        $this->assertFalse($this->subject->isGzip(Utils::streamFor(base64_decode(self::ENCODED_IMAGE))));
        if (function_exists('gzencode')) {
            $this->assertTrue($this->subject->isGzip(Utils::streamFor(gzencode('string'))));
        } else {
            $this->markTestIncomplete('No ZLIB installed');
        }
        $this->assertTrue($this->subject->isGzip(Utils::streamFor(Utils::streamFor(base64_decode(self::ENCODED_GZIP)))));
    }

    public function testIsGzipableMime(): void
    {
        $this->assertFalse($this->subject->isGzipableMime('text/html'));
        $this->assertTrue($this->subject->isGzipableMime(tao_helpers_File::MIME_SVG));
    }

    private function getFileStream($path): StreamInterface
    {
        return Utils::streamFor(fopen($path, 'rb'));
    }
}
