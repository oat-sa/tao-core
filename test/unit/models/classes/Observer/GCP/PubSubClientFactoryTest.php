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
 * Copyright (c) 2022 Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Observer\GCP;

use ErrorException;
use Google\Cloud\PubSub\PubSubClient;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Observer\GCP\PubSubClientFactory;

class PubSubClientFactoryTest extends TestCase
{
    public function testUpdateSuccessfully(): void
    {
        $this->markTestSkipped('Cannot be tested unless real GCP config exists');
    }

    public function testUpdateWithMissConfigurationThrowsException(): void
    {
        if (!class_exists(PubSubClient::class)) {
            $this->markTestSkipped();
        }

        $this->expectException(ErrorException::class);

        (new PubSubClientFactory([]))->create();
    }
}
