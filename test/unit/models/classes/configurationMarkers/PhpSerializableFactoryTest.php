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

use oat\tao\model\configurationMarkers\Secrets\EnvPhpSerializableSecret;
use oat\tao\model\configurationMarkers\Secrets\SerializableFactory;
use PHPUnit\Framework\TestCase;

class PhpSerializableFactoryTest extends TestCase
{
    private const TEST_INDEX = 'TEST_INDEX';

    public function testFactory(): void
    {
        $factory = new SerializableFactory();
        $object = $factory->create(self::TEST_INDEX);
        self::assertInstanceOf(EnvPhpSerializableSecret::class, $object);
        self::assertSame(self::TEST_INDEX, $object->getEnvIndex());
    }
}
