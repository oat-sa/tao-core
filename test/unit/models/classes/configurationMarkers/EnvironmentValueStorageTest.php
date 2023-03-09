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
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\configurationMarkers;

use oat\tao\model\configurationMarkers\Secrets\EnvironmentValueStorage;
use PHPUnit\Framework\TestCase;

class EnvironmentValueStorageTest extends TestCase
{
    public function testIndexAccess(): void
    {
        $env['PERSISTENCES_PGSQL_HOST'] = 'tao-postgres';
        $env['PERSISTENCES_PGSQL_USER'] = 'tao';
        $env['PERSISTENCES_PGSQL_PASSWORD'] = 'r00t';

        $storage = new EnvironmentValueStorage($env);

        foreach ($env as $key => $value) {
            self::assertSame($value, $storage->get($key));
        }
    }

    public function testIndexExist(): void
    {
        $env['PERSISTENCES_PGSQL_HOST'] = 'tao-postgres';
        $env['PERSISTENCES_PGSQL_USER'] = 'tao';
        $env['PERSISTENCES_PGSQL_PASSWORD'] = 'r00t';

        $storage = new EnvironmentValueStorage($env);

        foreach ($env as $key => $value) {
            self::assertTrue($storage->exist($key));
        }

        self::assertFalse($storage->exist('foobar'));
    }
}
