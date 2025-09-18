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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\search\Service;

use oat\tao\model\Lists\Business\Domain\ClassMetadataSearchRequest;
use oat\tao\model\search\ResultColumn;
use oat\tao\model\search\SearchSettings;
use oat\tao\model\search\Service\DefaultSearchSettingsService;
use PHPUnit\Framework\TestCase;

class DefaultSearchSettingsServiceTest extends TestCase
{
    /** @var DefaultSearchSettingsService */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new DefaultSearchSettingsService();
    }

    public function testsGetSettingsByClassMetadataSearchRequest(): void
    {
        $this->assertEquals(
            new SearchSettings(
                [
                    new ResultColumn(
                        'label',
                        'label',
                        __('Label'),
                        'text',
                        null,
                        null,
                        false,
                        true,
                        false
                    ),
                ]
            ),
            $this->subject->getSettingsByClassMetadataSearchRequest(
                $this->createMock(ClassMetadataSearchRequest::class)
            )
        );
    }
}
