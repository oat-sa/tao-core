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

namespace oat\tao\model\StatisticalMetadata\Import\Processor;

use core_kernel_classes_Resource;
use oat\tao\model\exceptions\UserErrorException;
use oat\tao\model\metadata\compiler\ResourceMetadataCompilerInterface;
use oat\tao\model\Observer\Subject;
use oat\tao\model\StatisticalMetadata\DataStore\Compiler\StatisticalJsonResourceMetadataCompiler;
use oat\tao\model\StatisticalMetadata\Import\Observer\ObserverFactory;
use Psr\Log\LoggerInterface;
use Throwable;

class NotifyImportService
{
    private const DEFAULT_MAX_TRIES = 10;
    private const DEFAULT_RETRY_DELAY = 1000000;

    /** @var LoggerInterface */
    private $logger;

    /** @var ResourceMetadataCompilerInterface */
    private $resourceMetadataCompiler;

    /** @var int */
    private $maxTries = self::DEFAULT_MAX_TRIES;

    /** @var int */
    private $retryDelay = self::DEFAULT_RETRY_DELAY;

    /** @var core_kernel_classes_Resource[] */
    private $resources = [];

    /** @var array */
    private $aliases = [];

    /** @var ObserverFactory */
    private $observerFactory;

    public function __construct(
        LoggerInterface $logger,
        ResourceMetadataCompilerInterface $jsonMetadataCompiler,
        ObserverFactory $observerFactory
    ) {
        $this->logger = $logger;
        $this->resourceMetadataCompiler = $jsonMetadataCompiler;
        $this->observerFactory = $observerFactory;
    }

    public function withMaxTries(int $maxTries): self
    {
        $this->maxTries = $maxTries;

        return $this;
    }

    public function withRetryDelay(int $retryDelay): self
    {
        $this->retryDelay = $retryDelay;

        return $this;
    }

    public function addResource(core_kernel_classes_Resource $resource): self
    {
        $this->resources[] = $resource;

        return $this;
    }

    public function withAliases(array $aliases): self
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * @throws UserErrorException
     */
    public function notify(): void
    {
        try {
            $this->doNotify();
        } catch (Throwable $exception) {
            $this->logger->error(
                sprintf(
                    'Error while syncing statistical data: "%s"',
                    $exception->getMessage()
                )
            );

            throw new UserErrorException(
                __('Unable to sync statistical data. Please, contact the system administrator for more details')
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function doNotify(): void
    {
        $data = [];
        $resourceIds = [];

        if ($this->resourceMetadataCompiler instanceof StatisticalJsonResourceMetadataCompiler) {
            $this->resourceMetadataCompiler->withAliases($this->aliases);
        }

        foreach ($this->resources as $resource) {
            $resourceIds[] = $resource->getUri();
            $data[] = $this->resourceMetadataCompiler->compile($resource);
        }

        $tries = 0;

        do {
            try {
                $tries++;

                $this->observerFactory
                    ->create()
                    ->update(new Subject($data));

                return;
            } catch (Throwable $exception) {
                usleep($this->retryDelay);

                $this->logger->error(
                    sprintf(
                        'Error (try: %s) while syncing statistical data: "%s", resources: "%s"',
                        $tries,
                        $exception->getMessage(),
                        implode(', ', $resourceIds)
                    )
                );
            }
        } while ($tries < $this->maxTries);

        throw $exception;
    }
}
