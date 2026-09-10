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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\mvc;

use oat\tao\model\mvc\DotEnvReader;
use PHPUnit\Framework\TestCase;

class DotEnvReaderTest extends TestCase
{
    private const TEST_ENV_KEY = 'TAO_DOTENV_READER_TEST_KEY';
    private const TEST_ENV_VALUE = 'dotenv-reader-test-value';

    private ?string $previousEnvValue = null;
    private ?string $previousServerValue = null;
    private string|false $previousGetEnvValue = false;
    private string $temporaryEnvFile;

    protected function setUp(): void
    {
        $this->previousEnvValue = $_ENV[self::TEST_ENV_KEY] ?? null;
        $this->previousServerValue = $_SERVER[self::TEST_ENV_KEY] ?? null;

        $this->previousGetEnvValue = getenv(self::TEST_ENV_KEY);

        $this->clearTestEnvironment();

        $this->temporaryEnvFile = tempnam(sys_get_temp_dir(), 'tao-dotenv-reader-test-');
        file_put_contents(
            $this->temporaryEnvFile,
            self::TEST_ENV_KEY . '=' . self::TEST_ENV_VALUE . PHP_EOL
        );
    }

    protected function tearDown(): void
    {
        $this->clearTestEnvironment();

        if ($this->previousEnvValue !== null) {
            $_ENV[self::TEST_ENV_KEY] = $this->previousEnvValue;
        }

        if ($this->previousServerValue !== null) {
            $_SERVER[self::TEST_ENV_KEY] = $this->previousServerValue;
        }

        if ($this->previousGetEnvValue !== false) {
            putenv(self::TEST_ENV_KEY . '=' . $this->previousGetEnvValue);
        }

        if (isset($this->temporaryEnvFile) && file_exists($this->temporaryEnvFile)) {
            unlink($this->temporaryEnvFile);
        }
    }

    public function testItLoadsEnvVariablesIntoEnvArrayAndGetEnv(): void
    {
        new DotEnvReader($this->temporaryEnvFile);

        $this->assertSame(self::TEST_ENV_VALUE, $_ENV[self::TEST_ENV_KEY] ?? null);
        $this->assertSame(self::TEST_ENV_VALUE, getenv(self::TEST_ENV_KEY));
    }

    private function clearTestEnvironment(): void
    {
        unset($_ENV[self::TEST_ENV_KEY], $_SERVER[self::TEST_ENV_KEY]);
        putenv(self::TEST_ENV_KEY);
    }
}
