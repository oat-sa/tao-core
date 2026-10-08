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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\integration\scripts\install;

use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\scripts\install\SetWProofreaderConfig;
use oat\tao\test\TaoPhpUnitTestRunner;

class SetWProofreaderConfigTest extends TaoPhpUnitTestRunner
{
    private const CONFIG_ID = 'tao/ckeditor/wproofreaderBootstrap';

    public function setUp(): void
    {
        TaoPhpUnitTestRunner::initTest();
    }

    public function testListsReplaceExistingConfig(): void
    {
        $registry = ClientLibConfigRegistry::getRegistry();
        $registry->remove(self::CONFIG_ID);
        $registry->set(self::CONFIG_ID, [
            'spellCheckConfig' => [
                'providerConfig' => [
                    'ignoreClasses' => ['old', 'obsolete'],
                    'ignoreElements' => ['old']
                ]
            ]
        ]);

        try {
            (new SetWProofreaderConfig())([
                'providerId' => 'wproofreader',
                'serviceId' => 'portal-key',
                'ignoreClasses' => ['new'],
                'ignoreElements' => []
            ]);

            $config = $registry->get(self::CONFIG_ID);
            $spellCheckConfig = $config['spellCheckConfig'];
            $this->assertTrue($spellCheckConfig['enabled']);
            $this->assertSame('wproofreader', $spellCheckConfig['providerId']);
            $this->assertSame('portal-key', $spellCheckConfig['providerConfig']['serviceId']);
            $this->assertSame(['new'], $spellCheckConfig['providerConfig']['ignoreClasses']);
            $this->assertSame([], $spellCheckConfig['providerConfig']['ignoreElements']);
        } finally {
            $registry->remove(self::CONFIG_ID);
        }
    }
}
