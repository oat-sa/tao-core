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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Command;

use InvalidArgumentException;

class ResourceTransferCommand
{
    public const ACL_KEEP_ORIGINAL = 'acl.keep.original';
    public const ACL_USE_DESTINATION = 'acl.use.destination';
    public const TRANSFER_MODE_COPY = 'copy';
    public const TRANSFER_MODE_MOVE = 'move';
    private const ACL_OPTIONS = [
        self::ACL_KEEP_ORIGINAL,
        self::ACL_USE_DESTINATION,
    ];
    private const TRANSFER_MODE_OPTIONS = [
        self::TRANSFER_MODE_COPY,
        self::TRANSFER_MODE_MOVE,
    ];

    private string $from;
    private string $to;
    private string $aclMode;
    private string $transferMode;

    public function __construct(string $from, string $to, ?string $aclMode, ?string $transferMode)
    {
        $aclMode = $aclMode ?? self::ACL_KEEP_ORIGINAL;
        $transferMode = $transferMode ?? self::TRANSFER_MODE_COPY;

        if (!in_array($aclMode, self::ACL_OPTIONS, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'ACL mode %s not supported. Only supported %s',
                    $aclMode,
                    implode(',', self::ACL_OPTIONS)
                )
            );
        }

        if (!in_array($transferMode, self::TRANSFER_MODE_OPTIONS, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Transfer mode %s not supported. Only supported %s',
                    $transferMode,
                    implode(',', self::TRANSFER_MODE_OPTIONS)
                )
            );
        }

        $this->from = $from;
        $this->to = $to;
        $this->aclMode = $aclMode;
        $this->transferMode = $transferMode;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function keepOriginalAcl(): bool
    {
        return $this->aclMode === self::ACL_KEEP_ORIGINAL;
    }

    public function useDestinationAcl(): bool
    {
        return $this->aclMode === self::ACL_USE_DESTINATION;
    }

    public function isCopyTo(): bool
    {
        return $this->transferMode === self::TRANSFER_MODE_COPY;
    }

    public function isMoveTo(): bool
    {
        return $this->transferMode === self::TRANSFER_MODE_MOVE;
    }
}
