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

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use common_ext_Extension as Extension;
use common_ext_ExtensionsManager as ExtensionsManager;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use common_ext_ExtensionException as ExtensionException;

/**
 * Class Version202009040615202234_tao
 *
 * @package oat\tao\migrations
 */
final class Version202009040615202234_tao extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Configure default `hideLogo` value for the login config.';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $extension = $this->getExtension();
        $config = $extension->getConfig('login');
        $config['hideLogo'] = false;
        $extension->setConfig('login', $config);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $extension = $this->getExtension();
        $config = $extension->getConfig('login');

        if (array_key_exists('hideLogo', $config)) {
            unset($config['hideLogo']);
        }

        $extension->setConfig('login', $config);
    }

    /**
     * @throws ExtensionException
     *
     * @return Extension
     */
    private function getExtension(): Extension
    {
        /** @var ExtensionsManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);

        return $extensionManager->getExtensionById('tao');
    }
}
