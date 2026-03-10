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
 * Copyright (c) 2013-2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

/**
 * PHPUnit test of the {@link tao_helpers_Duration} helper
 *
 * @package tao
 */

use PHPUnit\Framework\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\service\ApplicationService;

class DisplayHelperTest extends TestCase
{
    private $defaultEncoding = 'UTF-8';

    /**
     * @before
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function init(): void
    {
        $config = new common_persistence_KeyValuePersistence(new common_persistence_InMemoryKvDriver(), []);
        $config->set(ApplicationService::SERVICE_ID, $this->createApplicationServiceMock());

        ServiceManager::setServiceManager(new ServiceManager($config));
    }

    /**
     * Data provider for the testTimetoDuration method
     *
     * @return array[] the parameters
     */
    public function stringToCleanProvider(): array
    {
        return [
            ['This is a simple text', '_', -1, 'This_is_a_simple_text'],
            ['This is a simple text', '-', 10, 'This_is_a_'],
            ['@à|`', '-', -1, '-à--'],
            ['@à|`', '-', 2, '-à'],
            ['This 4s @ \'stronger\' tèxte', '', -1, 'This_4s__stronger_tèxte'],
        ];
    }

    /**
     * Test {@link tao_helpers_Display::}
     *
     * @dataProvider stringToCleanProvider
     */
    public function testCleaner(string $input, string $joker, int $maxLength, string $expected): void
    {
        $this->assertEquals($expected, tao_helpers_Display::textCleaner($input, $joker, $maxLength));
    }

    private function createApplicationServiceMock(): ApplicationService
    {
        $applicationServiceMock = $this->createMock(ApplicationService::class);

        $applicationServiceMock
            ->method('getDefaultEncoding')
            ->willReturn($this->defaultEncoding);

        return $applicationServiceMock;
    }
}
