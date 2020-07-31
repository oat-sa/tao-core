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

namespace oat\tao\model\task\migration;

class MigrationConfig
{
    /** @var int */
    private $chunkSize;

    /** @var int */
    private $start;

    /** @var int */
    private $pickSize;

    /** @var bool */
    private $processAll;

    /** @var int */
    private $end;


    public function __construct(
        int $chunkSize,
        int $start,
        int $pickSize,
        bool $processAll
    )
    {
        $this->chunkSize = $chunkSize;
        $this->start = $start;
        $this->pickSize = $pickSize;
        $this->processAll = $processAll;
    }

    public function isProcessAll(): bool
    {
        return $this->processAll;
    }

    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getPickSize(): int
    {
        return $this->pickSize;
    }
}
