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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\Csv\Factory;

use SplFileObject;
use InvalidArgumentException;
use oat\tao\model\Csv\Service\Reader;
use League\Csv\Reader as LeagueReader;

class ReaderFactory
{
    public function createFromFileObject(SplFileObject $fileObject): Reader
    {
        $reader = LeagueReader::createFromFileObject($fileObject);

        return new Reader($reader);
    }

    /**
     * @param resource $stream
     */
    public function createFromStream($stream): Reader
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('The provided value must be a resource.');
        }

        $reader = LeagueReader::createFromStream($stream);

        return new Reader($reader);
    }

    public function createFromString(string $content): Reader
    {
        $reader = LeagueReader::createFromString($content);

        return new Reader($reader);
    }

    /**
     * @param resource|null $context the resource context
     */
    public function createFromPath(string $path, string $mode = 'r', $context = null): Reader
    {
        if ($context !== null && !is_resource($context)) {
            throw new InvalidArgumentException('The provided value must be a resource or null.');
        }

        $reader = LeagueReader::createFromPath($path, $mode, $context);

        return new Reader($reader);
    }
}
