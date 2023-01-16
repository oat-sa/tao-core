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
 *
 */

namespace oat\tao\test\model;

use oat\tao\model\EnvPhpSerializable;
use PHPUnit\Framework\TestCase;

class EnvPhpSerializableTest extends TestCase
{
    private ?string $envHost;
    private ?string $envUser;
    private ?string $envPassword;

    public function setUp(): void
    {
        $this->envHost = $_ENV['PERSISTENCES_PGSQL_HOST'];
        $this->envUser = $_ENV['PERSISTENCES_PGSQL_USER'];
        $this->envPassword = $_ENV['PERSISTENCES_PGSQL_PASSWORD'];

        $_ENV['PERSISTENCES_PGSQL_HOST'] = 'tao-postgres';
        $_ENV['PERSISTENCES_PGSQL_USER'] = 'tao';
        $_ENV['PERSISTENCES_PGSQL_PASSWORD'] = 'r00t';
    }

    public function testIndexesAndValues()
    {
        $host = new EnvPhpSerializable('PERSISTENCES_PGSQL_HOST');
        $user = new EnvPhpSerializable('PERSISTENCES_PGSQL_USER');
        $password = new EnvPhpSerializable('PERSISTENCES_PGSQL_PASSWORD');

        self::assertSame('PERSISTENCES_PGSQL_HOST', $host->getEnvIndex());
        self::assertSame('PERSISTENCES_PGSQL_USER', $user->getEnvIndex());
        self::assertSame('PERSISTENCES_PGSQL_PASSWORD', $password->getEnvIndex());

        self::assertSame('tao-postgres', (string)$host);
        self::assertSame('tao', (string)$user);
        self::assertSame('r00t', (string)$password);
    }

    protected function tearDown(): void
    {
        $_ENV['PERSISTENCES_PGSQL_HOST'] = $this->envHost;
        $_ENV['PERSISTENCES_PGSQL_USER'] = $this->envUser;
        $_ENV['PERSISTENCES_PGSQL_PASSWORD'] = $this->envPassword;
    }
}