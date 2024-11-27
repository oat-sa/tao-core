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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\Translation\Service;

use core_kernel_classes_Resource;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class AbstractQtiIdentifierSetter
{
    public const OPTION_RESOURCE = 'resource';
    public const OPTION_IDENTIFIER = 'identifier';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function set(array $options): void
    {
        try {
            if (!isset($options[self::OPTION_RESOURCE], $options[self::OPTION_IDENTIFIER])) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Options %s and %s are required to set QTI Identifier.',
                        self::OPTION_RESOURCE,
                        self::OPTION_IDENTIFIER
                    )
                );
            }

            $this->applyIdentifier($options);
        } catch (Throwable $exception) {
            $this->logger->error('An error occurred while setting QTI identifier: ' . $exception->getMessage());

            throw $exception;
        }
    }

    abstract protected function applyIdentifier(array $options): void;

    protected function getResource(array $options): core_kernel_classes_Resource
    {
        return $options[self::OPTION_RESOURCE];
    }

    protected function getIdentifier(array $options): string
    {
        return $options[self::OPTION_IDENTIFIER];
    }
}
