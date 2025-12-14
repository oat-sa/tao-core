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
use oat\tao\model\configurationMarkers\Secrets\EnvironmentValueStorage;
use Psr\Log\LoggerInterface;

class ConfigurationMarkers
{
    /** @var string */
    private const MARKER_PATTERN = '/\$ENV{([a-zA-Z0-9\-\_]+)}/';
    private LoggerInterface $logger;
    private SerializableSecretDtoFactory $serializableFactory;
    private Report $report;
    private array $envVars;
    private array $matchedVars = [];

    public function __construct(
        SerializableSecretDtoFactory $serializableFactory,
        LoggerInterface $logger,
        ?array $envVars = null
    ) {
        $this->serializableFactory = $serializableFactory;
        $this->logger = $logger;
        $this->envVars = $envVars ?? $_ENV;
    }

    public function replaceMarkers(array $configurationWithMarkers): array
    {
        $this->report = Report::createInfo('Starting ConfigurationMarkers.');
        if (empty($configurationWithMarkers)) {
            throw new InvalidArgumentException('Empty configuration.');
        }

        array_walk_recursive($configurationWithMarkers, [$this, 'walkReplaceMarkers']);

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

    private function walkReplaceMarkers(&$item): void
    {
        if (is_string($item) === false) {
            return;
        }
        $matches = $this->findMatches($item);
        if (empty($matches)) {
            return;
        }

        $isSecretDefined = $this->envVars[$matches[1]] ?? false;
        $this->printMatchNotification((bool) $isSecretDefined, $matches[1]);
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
            if (!is_array($value)) {
                if (is_string($item) === false) {
                    continue;
                }
                $matches = $this->findMatches($value);
                if (empty($matches)) {
                    unset($item[$key]);
                }
                continue;
            }
            $size = $this->unsetRecursive($value);
            if (!$size) {
                unset($item[$key]);
            }
        }
        return count($item) > 0;
    }

    private function findMatches(string $item): array
    {
        $matches = [];
        preg_match(self::MARKER_PATTERN, $item, $matches);

        return $matches;
    }

    private function printMatchNotification(bool $isSecretDefined, string $secretName): void
    {
        if (in_array($secretName, $this->matchedVars)) {
            return;
        }
        array_push($this->matchedVars, $secretName);
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
