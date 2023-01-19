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

namespace oat\tao\test\unit\install\utils;

use oat\tao\install\utils\ConfigurationMarkers;
use oat\tao\model\EnvPhpSerializable;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConfigurationMarkersTest extends TestCase
{
    public function testReplacingMarkers(): void
    {
        $configuration = [
            'connection' => [
                'driver' => 'pdo_pgsql',
                'dbname' => 'tao',
                'host' => '$ENV{PERSISTENCES_PGSQL_HOST}',
                'user' => '$ENV{PERSISTENCES_PGSQL_USER}',
                'password' => '$ENV{PERSISTENCES_PGSQL_PASSWORD}',
                'non_existing_entry_in_env' => '$ENV{NON_EXISTING_ENTRY_IN_ENV}',
                'driverOptions' => []
            ]
        ];
        $env = [
            'PERSISTENCES_PGSQL_HOST' => 'tao-postgres',
            'PERSISTENCES_PGSQL_USER' => 'tao',
            'PERSISTENCES_PGSQL_PASSWORD' => 'r00t',
        ];

        $loggerMock = $this->createMock(LoggerInterface::class);

        $markers = new ConfigurationMarkers($loggerMock);
        $markers
            ->setSecretsStorage($env)
            ->setConfigurationWithMarkers($configuration);

        $replaced = $markers->replace();

        self::assertArrayHasKey('connection', $replaced);
        self::assertCount(7, $replaced['connection']);
        self::assertArrayHasKey('driver', $replaced['connection']);
        self::assertArrayHasKey('dbname', $replaced['connection']);
        self::assertArrayHasKey('host', $replaced['connection']);
        self::assertArrayHasKey('user', $replaced['connection']);
        self::assertArrayHasKey('password', $replaced['connection']);
        self::assertArrayHasKey('non_existing_entry_in_env', $replaced['connection']);
        self::assertArrayHasKey('driverOptions', $replaced['connection']);

        self::assertSame(
            '',
            $replaced['connection']['non_existing_entry_in_env']
        );

        self::assertInstanceOf(EnvPhpSerializable::class, $replaced['connection']['host']);
        self::assertInstanceOf(EnvPhpSerializable::class, $replaced['connection']['user']);
        self::assertInstanceOf(EnvPhpSerializable::class, $replaced['connection']['password']);

        self::assertSame('PERSISTENCES_PGSQL_HOST', $replaced['connection']['host']->getEnvIndex());
        self::assertSame('PERSISTENCES_PGSQL_USER', $replaced['connection']['user']->getEnvIndex());
        self::assertSame('PERSISTENCES_PGSQL_PASSWORD', $replaced['connection']['password']->getEnvIndex());
    }

    public function testNotifications(): void
    {
        $configuration = [
            'connection' => [
                'password' => '$ENV{PERSISTENCES_PGSQL_PASSWORD}',
            ]
        ];
        $env = [
            'PERSISTENCES_PGSQL_PASSWORD' => 'r00t',
        ];
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->atLeast(1))->method('info');

        $markers = new ConfigurationMarkers($loggerMock);
        $markers
            ->setSecretsStorage($env)
            ->setConfigurationWithMarkers($configuration);

        $markers->replace();
    }

    public function testEmptyConfiguration(): void
    {
        $configuration = [];

        $loggerMock = $this->createMock(LoggerInterface::class);
        $this->expectException(\InvalidArgumentException::class);
        $markers = new ConfigurationMarkers($loggerMock);
        $markers->setConfigurationWithMarkers($configuration);
        $markers->replace();
    }

    public function testEmptySecretsStorage(): void
    {
        $configuration = [
            'connection' => [
                'password' => '$ENV{PERSISTENCES_PGSQL_PASSWORD}',
            ]
        ];

        $loggerMock = $this->createMock(LoggerInterface::class);
        $markers = new ConfigurationMarkers($loggerMock);
        $markers
            ->setSecretsStorage([])
            ->setConfigurationWithMarkers($configuration);;

        $replaced = $markers->replace();

        self::assertArrayHasKey('connection', $replaced);
        self::assertArrayHasKey('password', $replaced['connection']);
        self::assertSame('', $replaced['connection']['password']);
    }

    public function testNoMatchedMarker(): void
    {
        $configuration = [
            'connection' => [
                'password' => '$ENV{NOT_MATCHING_MARKER}',
            ]
        ];
        $env = [
            'PERSISTENCES_PGSQL_PASSWORD' => 'r00t',
        ];

        $loggerMock = $this->createMock(LoggerInterface::class);

        $markers = new ConfigurationMarkers($loggerMock);
        $markers
            ->setSecretsStorage($env)
            ->setConfigurationWithMarkers($configuration);;

        $replaced = $markers->replace();

        self::assertSame('', $replaced['connection']['password']);
    }
}
