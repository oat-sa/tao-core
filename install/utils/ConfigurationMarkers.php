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

namespace oat\tao\install\utils;

use oat\tao\model\EnvPhpSerializableFactory;
use Psr\Log\LoggerInterface;

class ConfigurationMarkers
{
    /** @var string */
    private const MARKER_PATTERN = '/\$ENV{(.*)}/';
    private LoggerInterface $logger;
    private EnvPhpSerializableFactory $serializableFactory;
    private ?array $secretsStorage;

    public function __construct(
        array $secretsStorage,
        EnvPhpSerializableFactory $serializableFactory,
        LoggerInterface $logger
    )
    {
        $this->secretsStorage = $secretsStorage;
        $this->serializableFactory = $serializableFactory;
        $this->logger = $logger;
    }

    public function replaceMarkers(array $configurationWithMarkers): array
    {
        if (empty($configurationWithMarkers)) {
            throw new \InvalidArgumentException('Empty configuration.');
        }

        array_walk_recursive($configurationWithMarkers, 'self::walk');

        return $configurationWithMarkers;
    }

    private function walk(&$item): void
    {
        if (is_string($item) && (int)preg_match(self::MARKER_PATTERN, $item, $matches) > 0) {
            $isSecretDefined = isset($this->secretsStorage[$matches[1] ?? '']);
            $this->printMatchNotification($isSecretDefined, $matches[1]);
            if (!$isSecretDefined) {
                //remove not found markers from config array as reference
                $item = '';
                return;
            }
            $item = $this->serializableFactory->create($matches[1]);
            $this->info(sprintf('Converted config %s value to PHP Serializable.', $item));
        }
    }

    private function printMatchNotification(bool $isSecretDefined, string $secretName): void
    {
        $message = sprintf('Found seed file marker: %s', $secretName);
        if ($isSecretDefined) {
            $message .= ' and its Secrets Storage value.';
        } else {
            $message .= ' but NO CORRESPONDING value in Secrets Storage!';
        }
        $this->info($message);
    }

    private function info(string $message): void
    {
        $this->logger->info($message);
    }
}
