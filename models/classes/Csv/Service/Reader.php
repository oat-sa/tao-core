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

namespace oat\tao\model\Csv\Service;

use Iterator;
use League\Csv\Exception;
use InvalidArgumentException;
use League\Csv\InvalidArgument;
use League\Csv\Reader as LeagueCsvReader;

class Reader
{
    /** @var LeagueCsvReader */
    private $reader;

    public function __construct(LeagueCsvReader $reader)
    {
        $this->reader = $reader;
    }

    public function setDelimiter(string $delimiter): self
    {
        try {
            $this->reader->setDelimiter($delimiter);
        } catch (Exception | InvalidArgument $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }

        return $this;
    }

    public function setHeaderOffset(?int $offset): self
    {
        try {
            $this->reader->setHeaderOffset($offset);
        } catch (Exception | InvalidArgument $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getHeader(): array
    {
        return $this->reader->getHeader();
    }

    /**
     * @param string[] $header an optional header to use instead of the CSV document header
     */
    public function getRecords(array $header = []): Iterator
    {
        return $this->reader->getRecords($header);
    }

    public function count(): int
    {
        return $this->reader->count();
    }
}
