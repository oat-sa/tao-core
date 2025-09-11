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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\security\DataAccess\Repository;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use oat\tao\model\security\Business\Domain\Setting;
use oat\tao\model\security\Business\Domain\SettingsCollection;
use oat\tao\model\security\DataAccess\Repository\SecuritySettingsRepository;
use oat\tao\model\service\SettingsStorage;

class SecuritySettingsRepositoryTest extends TestCase
{
    /** @var string[] */
    private $storageData = [];

    /** @var MockObject|SettingsStorage */
    private $storage;

    /** @var SecuritySettingsRepository */
    private $sut;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(SettingsStorage::class);

        $this->storage
            ->method('get')
            ->willReturnCallback([$this, 'getFromStorage']);

        $this->storage
            ->method('set')
            ->willReturnCallback([$this, 'setIntoStorage']);

        $this->initSut();
    }

    public function initSut(): void
    {
        $this->sut = new SecuritySettingsRepository($this->storage);
    }

    /**
     * @dataProvider persistenceDataProvider
     *
     * @param array              $expected
     * @param SettingsCollection $settings
     * @param string[]           $storageData
     */
    public function testPersist(array $expected, SettingsCollection $settings, array $storageData = []): void
    {
        $this->storageData = $storageData;

        $this->sut->persist($settings);

        $this->assertEquals($this->storageData, $expected);
    }

    /**
     * @dataProvider searchDataProvider
     *
     * @param SettingsCollection $expected
     * @param array              $storageData
     */
    public function testFindAll(SettingsCollection $expected, array $storageData = []): void
    {
        $this->storageData = $storageData;

        $this->assertEquals($expected, $this->sut->findAll());
    }

    public function persistenceDataProvider(): array
    {
        return [
            'Empty'               => [
                'expected' => [],
                'settings' => new SettingsCollection(),
            ],
            'Write CSP'           => [
                'expected' => [
                    SecuritySettingsRepository::CONTENT_SECURITY_POLICY => 'some value',
                ],
                'settings' => new SettingsCollection(
                    new Setting(SecuritySettingsRepository::CONTENT_SECURITY_POLICY, 'some value')
                ),
            ],
            'Overwrite CSP'       => [
                'expected'    => [
                    SecuritySettingsRepository::CONTENT_SECURITY_POLICY => 'some other value',
                ],
                'settings'    => new SettingsCollection(
                    new Setting(SecuritySettingsRepository::CONTENT_SECURITY_POLICY, 'some other value')
                ),
                'storageData' => [
                    SecuritySettingsRepository::CONTENT_SECURITY_POLICY => 'some value',
                ],
            ],
            'Add CSP'             => [
                'expected'    => [
                    SecuritySettingsRepository::CONTENT_SECURITY_POLICY           => 'some value',
                    SecuritySettingsRepository::CONTENT_SECURITY_POLICY_WHITELIST => '',
                ],
                'settings'    => new SettingsCollection(
                    new Setting(SecuritySettingsRepository::CONTENT_SECURITY_POLICY, 'some value')
                ),
                'storageData' => [
                    SecuritySettingsRepository::CONTENT_SECURITY_POLICY_WHITELIST => '',
                ],
            ],
            'Write CSP whitelist' => [
                'expected' => [
                    SecuritySettingsRepository::CONTENT_SECURITY_POLICY           => 'list',
                    SecuritySettingsRepository::CONTENT_SECURITY_POLICY_WHITELIST => json_encode(
                        [
                            '*',
                            'www.example.com',
                        ]
                    ),
                ],
                'settings' => new SettingsCollection(
                    new Setting(SecuritySettingsRepository::CONTENT_SECURITY_POLICY, 'list'),
                    new Setting(
                        SecuritySettingsRepository::CONTENT_SECURITY_POLICY_WHITELIST,
                        "   * \t\r\n www.example.com\n   \r\t"
                    )
                ),
            ],
            'Skip CSP whitelist' => [
                'expected' => [
                    SecuritySettingsRepository::CONTENT_SECURITY_POLICY           => 'some value',
                ],
                'settings' => new SettingsCollection(
                    new Setting(SecuritySettingsRepository::CONTENT_SECURITY_POLICY, 'some value'),
                    new Setting(
                        SecuritySettingsRepository::CONTENT_SECURITY_POLICY_WHITELIST,
                        '*.example.com'
                    )
                ),
            ],
            'Write TLS'           => [
                'expected' => [
                    SecuritySettingsRepository::STRICT_TRANSPORT_SECURITY => '1',
                ],
                'settings' => new SettingsCollection(
                    new Setting(SecuritySettingsRepository::STRICT_TRANSPORT_SECURITY, '1')
                ),
            ],
            'Overwrite TLS'       => [
                'expected'    => [
                    SecuritySettingsRepository::STRICT_TRANSPORT_SECURITY => '0',
                ],
                'settings'    => new SettingsCollection(
                    new Setting(SecuritySettingsRepository::STRICT_TRANSPORT_SECURITY, '0')
                ),
                'storageData' => [
                    SecuritySettingsRepository::STRICT_TRANSPORT_SECURITY => '1',
                ],
            ],
        ];
    }

    public function searchDataProvider(): array
    {
        return [
            'Not initialized'   => [
                'expected' => $this->createSettingsCollection(),
            ],
            'CSP set'           => [
                'expected'    => $this->createSettingsCollection(
                    [
                        SecuritySettingsRepository::CONTENT_SECURITY_POLICY => 'some value',
                    ]
                ),
                'storageData' => [
                    SecuritySettingsRepository::CONTENT_SECURITY_POLICY => 'some value',
                ],
            ],
            'CSP whitelist set' => [
                'expected'    => $this->createSettingsCollection(
                    [
                        SecuritySettingsRepository::CONTENT_SECURITY_POLICY_WHITELIST => "*\nwww.example.com",
                    ]
                ),
                'storageData' => [
                    SecuritySettingsRepository::CONTENT_SECURITY_POLICY_WHITELIST => json_encode(
                        ['*', 'www.example.com']
                    ),
                ],
            ],
            'TLS set'           => [
                'expected'    => $this->createSettingsCollection(
                    [
                        SecuritySettingsRepository::STRICT_TRANSPORT_SECURITY => '1',
                    ]
                ),
                'storageData' => [
                    SecuritySettingsRepository::STRICT_TRANSPORT_SECURITY => '1',
                ],
            ],
        ];
    }

    public function getFromStorage(string $key)
    {
        /** @noinspection ProperNullCoalescingOperatorUsageInspection */
        return $this->storageData[$key] ?? false;
    }

    public function setIntoStorage(string $key, string $value): void
    {
        $this->storageData[$key] = $value;
    }

    private function createSettingsCollection(array $raw = []): SettingsCollection
    {
        $raw += [
            SecuritySettingsRepository::CONTENT_SECURITY_POLICY           => '',
            SecuritySettingsRepository::CONTENT_SECURITY_POLICY_WHITELIST => '',
            SecuritySettingsRepository::STRICT_TRANSPORT_SECURITY         => '',
        ];

        $settings = [];

        foreach ($raw as $key => $value) {
            $settings[] = new Setting($key, $value);
        }

        return new SettingsCollection(...$settings);
    }
}
