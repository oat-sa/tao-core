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

namespace oat\tao\install\utils;

use oat\tao\model\EnvPhpSerializable;
use Psr\Log\LoggerInterface;

class ConfigurationMarkers
{
    /** @var string */
    private const MARKER_PATTERN = '/\$ENV{(.*)}/';
    private array $configurationWithMarkers;
    private ?LoggerInterface $logger = null;
    private string $serializableClass = EnvPhpSerializable::class;
    private ?array $secretsStorage;

    public function __construct(array $configurationWithMarkers)
    {
        $this->configurationWithMarkers = $configurationWithMarkers;
        if (empty($this->configurationWithMarkers)) {
            throw new InvalidArgumentException('Empty configuration');
        }
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function setSerializableClass(string $serializableClass): self
    {
        $this->serializableClass = $serializableClass;

        return $this;
    }

    public function setSecretsStorage(array $secretsStorage): self
    {
        $this->secretsStorage = $secretsStorage;

        return $this;
    }

    public function replace(): array
    {
        array_walk_recursive($this->configurationWithMarkers, 'self::walk');

        return $this->configurationWithMarkers;
    }

    private function walk(&$item): void
    {
        if (is_string($item) && (int)preg_match(self::MARKER_PATTERN, $item, $matches) > 0) {
            $this->printMatchNotification($matches);
            if (isset($this->secretsStorage[$matches[1] ?? '']) === false) {
                return;
            }
            $this->convertToSerializable($item, $matches[1]);
        }
    }

    private function printMatchNotification(array $match): void
    {
        if (empty($match)) {
            return;
        }

        $message = sprintf('Found seed file marker: %s', $match[0]);
        if (isset($this->secretsStorage[$match[1] ?? ''])) {
            $message .= sprintf(' and Secrets Storage value "%s"', $this->secretsStorage[$match[1] ?? '']);
        } else {
            $message .= ' but NO CORRESPONDING value in Secrets Storage!';
        }
        $this->info($message);
    }

    private function convertToSerializable(string &$item, string $match): void
    {
        $item = new $this->serializableClass($match);
        $this->info(sprintf('Converted config %s value to PHP Serializable.', $item));
    }

    private function info(string $message): void
    {
        if ($this->logger === null) {
            return;
        }
        $this->logger->info($message);
    }
}
