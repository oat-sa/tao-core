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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\configurationMarkers;

use InvalidArgumentException;
use oat\oatbox\reporting\Report;
use oat\tao\model\configurationMarkers\Secrets\SerializableFactory;
use oat\tao\model\configurationMarkers\Secrets\Storage;
use Psr\Log\LoggerInterface;

class ConfigurationMarkers
{
    /** @var string */
    private const MARKER_PATTERN = '/\$ENV{([a-zA-Z0-9\-\_]+)}/';
    private LoggerInterface $logger;
    private SerializableFactory $serializableFactory;
    private Storage $secretsStorage;
    private Report $report;
    private bool $hasErrors = false;

    public function __construct(
        Storage $secretsStorage,
        SerializableFactory $serializableFactory,
        LoggerInterface $logger
    ) {
        $this->secretsStorage = $secretsStorage;
        $this->serializableFactory = $serializableFactory;
        $this->logger = $logger;
    }

    public function replaceMarkers(array $configurationWithMarkers): array
    {
        $this->report = Report::createInfo('Starting ConfigurationMarkers.');
        if (empty($configurationWithMarkers)) {
            throw new InvalidArgumentException('Empty configuration.');
        }

        array_walk_recursive($configurationWithMarkers, 'self::walkReplaceMarkers');

        return $configurationWithMarkers;
    }

    public function removeIndexesWithoutMarkers(array $configurationWithMarkers): array
    {
        if (empty($configurationWithMarkers)) {
            throw new InvalidArgumentException('Empty configuration.');
        }

        $this->unsetRecursive($configurationWithMarkers);

        return $configurationWithMarkers;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    private function walkReplaceMarkers(&$item): void
    {
        $matches = $this->findMatches($item);
        if (empty($matches)) {
            return;
        }

        $isSecretDefined = $this->secretsStorage->exist($matches[1] ?? '');
        $this->printMatchNotification($isSecretDefined, $matches[1]);
        if (!$isSecretDefined) {
            //remove not found markers from config array as reference
            $item = '';
            return;
        }
        $item = $this->serializableFactory->create($matches[1]);
    }

    private function unsetRecursive(&$item): bool
    {
        foreach ($item as $key => &$value) {
            if (is_array($value)) {
                $arraySize = $this->unsetRecursive($value);
                if (!$arraySize) {
                    unset($item[$key]);
                }
            } else {
                $matches = $this->findMatches($value);
                if (empty($matches)) {
                    unset($item[$key]);
                }
            }
        }
        return count($item) > 0;
    }

    private function findMatches($item): array
    {
        $matches = [];
        if (is_string($item) === false) {
            return $matches;
        }
        preg_match(self::MARKER_PATTERN, $item, $matches);

        return $matches;
    }

    private function printMatchNotification(bool $isSecretDefined, string $secretName): void
    {
        $message = sprintf('Found seed file marker: %s', $secretName);
        if ($isSecretDefined) {
            $message .= ' and its Secrets Storage value.';
            $this->notice($message);
            $this->reportSuccess($message);
            return;
        }
        $message .= ' but no corresponding value in Secrets Storage!';
        $this->error($message);
        $this->reportError($message);
        $this->hasErrors = true;
    }

    private function reportError(string $message): void
    {
        $this->report->add(Report::createError($message));
    }
    private function reportSuccess(string $message): void
    {
        $this->report->add(Report::createSuccess($message));
    }

    private function notice(string $message): void
    {
        $this->logger->notice($message);
    }

    private function error(string $message): void
    {
        $this->logger->error($message);
    }
}
