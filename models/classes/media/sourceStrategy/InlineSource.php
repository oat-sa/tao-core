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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA ;
 */

declare(strict_types=1);

namespace oat\tao\model\media\sourceStrategy;

use common_Exception;
use GuzzleHttp\Psr7\Stream;
use League\MimeTypeDetection\GeneratedExtensionToMimeTypeMap;
use oat\tao\model\media\MediaBrowser;
use oat\tao\model\media\mediaSource\DirectorySearchQuery;
use RuntimeException;

class InlineSource implements MediaBrowser
{
    public const FILENAME_PREFIX = 'inline-media-';

    public function getDirectories(DirectorySearchQuery $params): array
    {
        throw new common_Exception(__FUNCTION__ . ' not implemented');
    }

    public function getDirectory($parentLink = '/', $acceptableMime = [], $depth = 1)
    {
        throw new common_Exception(__FUNCTION__ . ' not implemented');
    }

    public function getFileInfo($link)
    {
        throw new common_Exception(__FUNCTION__ . ' not implemented');
    }

    public function download($link)
    {
        $stream = $this->getFileStream($link);

        return $stream->getContents();
    }

    public function getFileStream($link)
    {
        return new Stream($this->createResource($link));
    }

    public function getBaseName($link)
    {
        return self::FILENAME_PREFIX . md5($link) . '.' . $this->detectFileExtension($link);
    }

    private function detectFileExtension($link): ?string
    {
        $stream = $this->getFileStream($link);
        $mimetype = $stream->getMetadata('mediatype');
        $extension = array_search($mimetype, GeneratedExtensionToMimeTypeMap::MIME_TYPES_FOR_EXTENSIONS);
        if (!$extension) {
            throw new RuntimeException(
                sprintf('Could not determined inline asset file extension from the mime type: %s.', $mimetype)
            );
        }

        return $extension;
    }

    private function createResource($link)
    {
        $this->verifyDataUrlScheme($link);

        $resource = @fopen($link, 'r');
        if (!$resource) {
            throw new RuntimeException('Invalid Data URL, could not create a resource.');
        }

        return $resource;
    }

    private function verifyDataUrlScheme($link): void
    {
        $urlScheme = parse_url($link, PHP_URL_SCHEME);
        if ($urlScheme !== 'data') {
            throw new RuntimeException('Provided URL should match the Data (RFC 2397) scheme.');
        }
    }
}
