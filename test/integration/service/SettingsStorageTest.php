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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\test\integration\service;

use common_persistence_Manager;
use oat\generis\test\TestCase;
use oat\tao\model\service\SettingsStorage;

class SettingsStorageTest extends TestCase
{
    /**
     * @var SettingsStorage
     */
    private $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new SettingsStorage([
            SettingsStorage::OPTION_PERSISTENCE => 'default_kv',
            SettingsStorage::OPTION_KEY_NAMESPACE => 'settings:namespace',
        ]);

        $persistenceManager = new common_persistence_Manager([
            common_persistence_Manager::OPTION_PERSISTENCES => [
                'default_kv' => [
                    'driver' => 'no_storage'
                ]
            ]
        ]);
        $slMock = $this->getServiceLocatorMock([
            common_persistence_Manager::SERVICE_ID => $persistenceManager
        ]);
        $this->object->setServiceLocator($slMock);
    }

    public function testNotExistingPersistence()
    {
        $this->object->setOption(SettingsStorage::OPTION_PERSISTENCE, 'NOT_EXISTING_PERSISTENCE');

        $result = $this->object->set('DUMMY_KEY', 'DUMMY_VALUE');
        $this->assertFalse($result, 'Result must be as expected when service uses not existing persistence');
    }
}
