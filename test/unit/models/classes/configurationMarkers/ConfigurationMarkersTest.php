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

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\configurationMarkers;

use InvalidArgumentException;
use oat\tao\model\configurationMarkers\ConfigurationMarkers;
use oat\tao\model\configurationMarkers\Secrets\SerializableSecretDto;
use oat\tao\model\configurationMarkers\SerializableSecretDtoFactory;
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
                'host' => '$ENV{FAKE_PERSISTENCES_PGSQL_HOST}',
                'user' => '$ENV{FAKE_PERSISTENCES_PGSQL_USER}',
                'password' => '$ENV{FAKE_PERSISTENCES_PGSQL_PASSWORD}',
                'non_existing_entry_in_env' => '$ENV{NON_EXISTING_ENTRY_IN_ENV}',
                'driverOptions' => []
            ]
        ];

        $envVars['FAKE_PERSISTENCES_PGSQL_HOST'] = 'fake_host';
        $envVars['FAKE_PERSISTENCES_PGSQL_USER'] = 'fake_user';
        $envVars['FAKE_PERSISTENCES_PGSQL_PASSWORD'] = 'fake_pass';

        $loggerMock = $this->createMock(LoggerInterface::class);

        $markers = new ConfigurationMarkers(
            new SerializableSecretDtoFactory(),
            $loggerMock,
            $envVars
        );

        $replaced = $markers->replaceMarkers($configuration);

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

        self::assertInstanceOf(SerializableSecretDto::class, $replaced['connection']['host']);
        self::assertInstanceOf(SerializableSecretDto::class, $replaced['connection']['user']);
        self::assertInstanceOf(SerializableSecretDto::class, $replaced['connection']['password']);

        self::assertSame('FAKE_PERSISTENCES_PGSQL_HOST', $replaced['connection']['host']->getEnvIndex());
        self::assertSame('FAKE_PERSISTENCES_PGSQL_USER', $replaced['connection']['user']->getEnvIndex());
        self::assertSame('FAKE_PERSISTENCES_PGSQL_PASSWORD', $replaced['connection']['password']->getEnvIndex());
    }

    public function testNotifications(): void
    {
        $configuration = [
            'connection' => [
                'password' => '$ENV{FAKE_PERSISTENCES_PGSQL_PASSWORD}',
            ]
        ];

        $envVars['FAKE_PERSISTENCES_PGSQL_PASSWORD'] = 'fake_pass';
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->atLeast(1))->method('notice');

        $markers = new ConfigurationMarkers(
            new SerializableSecretDtoFactory(),
            $loggerMock,
            $envVars
        );

        $markers->replaceMarkers($configuration);
    }

    public function testEmptyConfiguration(): void
    {
        $configuration = [];
        $loggerMock = $this->createMock(LoggerInterface::class);
        $markers = new ConfigurationMarkers(
            new SerializableSecretDtoFactory(),
            $loggerMock
        );
        $this->expectException(InvalidArgumentException::class);
        $markers->replaceMarkers($configuration);
    }

    public function testEmptySecretsStorage(): void
    {
        $configuration = [
            'connection' => [
                'password' => '$ENV{PERSISTENCES_PGSQL_PASSWORD}',
            ]
        ];
        $loggerMock = $this->createMock(LoggerInterface::class);
        $markers = new ConfigurationMarkers(
            new SerializableSecretDtoFactory(),
            $loggerMock
        );

        $replaced = $markers->replaceMarkers($configuration);

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

        $loggerMock = $this->createMock(LoggerInterface::class);

        $markers = new ConfigurationMarkers(
            new SerializableSecretDtoFactory(),
            $loggerMock
        );

        $replaced = $markers->replaceMarkers($configuration);

        self::assertSame('', $replaced['connection']['password']);
    }
}
