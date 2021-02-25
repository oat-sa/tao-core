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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\install\utils;

use tao_install_utils_Exception as InstallUtilsException;

class ConfigWriter
{
    private const CONSTANT_PATTERN = '/(\'%s\')(.*?)$/ms';

    /** @var string */
    private $sample;

    /** @var string */
    private $file;

    public function __construct(string $sample, string $file)
    {
        if (!file_exists($sample)) {
            throw new InstallUtilsException('Unable to find sample config ' . $sample);
        }

        $this->sample = $sample;
        $this->file = $file;
    }

    public function createConfig(array $constants = []): void
    {
        $this->checkConfig();

        $content = file_get_contents($this->sample);

        if (!empty($content)) {
            foreach ($constants as $name => $value) {
                if (is_string($value)) {
                    $this->addStringConstant($content, $name, $value);
                } elseif (is_bool($value)) {
                    $this->addBoolConstant($content, $name, $value);
                } elseif (is_numeric($value)) {
                    $this->addNumericConstant($content, $name, $value);
                }

                if (!defined($name)) {
                    define($name, $value);
                }
            }

            file_put_contents($this->file, $content);
        }
    }

    private function checkConfig(): void
    {
        if (!is_writable(dirname($this->file))) {
            throw new InstallUtilsException(
                'Unable to create configuration file. Please set write permission to : ' . dirname($this->file)
            );
        }

        if (file_exists($this->file) && !is_writable($this->file)) {
            throw new InstallUtilsException(
                'Unable to create the configuration file. Please set the write permissions to : ' . $this->file
            );
        }

        if (!is_readable($this->sample)) {
            throw new InstallUtilsException(
                'Unable to read the sample configuration. Please set the read permissions to : ' . $this->sample
            );
        }
    }

    private function addStringConstant(string &$content, string $name, string $value): void
    {
        $processedValue = addslashes((string) $value);
        $content = preg_replace(
            sprintf(self::CONSTANT_PATTERN, $name),
            sprintf('$1,\'%s\');', $processedValue),
            $content
        );
    }

    private function addBoolConstant(string &$content, string $name, bool $value): void
    {
        $processedValue = 'false';

        if ($value === true) {
            $processedValue = 'true';
        }

        $content = preg_replace(
            sprintf(self::CONSTANT_PATTERN, $name),
            sprintf('$1, %s);', $processedValue),
            $content
        );
    }

    private function addNumericConstant(string &$content, string $name, int $value): void
    {
        $content = preg_replace(
            sprintf(self::CONSTANT_PATTERN, $name),
            sprintf('$1, %s);', $value),
            $content
        );
    }
}
