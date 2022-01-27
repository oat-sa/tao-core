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
 * Copyright (c) 2022 Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use common_ext_Extension;
use common_ext_ExtensionsManager;
use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\password\PasswordConstraintsService;
use oat\tao\model\password\PasswordConstraintsServiceInterface;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202201200829462234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Generate configuration for password constraints service.';
    }

    public function up(Schema $schema): void
    {
        /** @var common_ext_Extension $generisExtension */
        $generisExtension = $this->getServiceLocator()
            ->get(common_ext_ExtensionsManager::SERVICE_ID)
            ->getExtensionById('generis');

        $removeExistingPasswordConfig = false;
        $passwordConfig = $generisExtension->hasConfig('passwords')
            ? $generisExtension->getConfig('passwords')
            : [];

        if (!empty($passwordConfig) && array_key_exists('constrains', $passwordConfig)) {
            $passwordConstraintsService = new PasswordConstraintsService(array(
                PasswordConstraintsServiceInterface::OPTION_CONSTRAINTS => $passwordConfig['constrains']
            ));
            $removeExistingPasswordConfig = true;
        } else {
            $passwordConstraintsService = require_once(__DIR__ . '/../config/default/passwordConstraints.conf.php');
        }

        $this->registerService(PasswordConstraintsServiceInterface::SERVICE_ID, $passwordConstraintsService);

        if ($removeExistingPasswordConfig) {
            unset($passwordConfig['constrains']);
            $generisExtension->setConfig('passwords', $passwordConfig);
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->getServiceManager()->has(PasswordConstraintsServiceInterface::SERVICE_ID)) {
            $passwordConstraintsService = $this->getServiceManager()->get(PasswordConstraintsServiceInterface::SERVICE_ID);
        } else {
            $passwordConstraintsService = require_once(__DIR__ . '/../config/default/passwordConstraints.conf.php');
        }

        $generisExtension = $this->getServiceLocator()
            ->get(common_ext_ExtensionsManager::SERVICE_ID)
            ->getExtensionById('generis');

        $passwordConfig = $generisExtension->getConfig('passwords');
        $passwordConfig['constrains'] = $passwordConstraintsService->getOption(PasswordConstraintsServiceInterface::OPTION_CONSTRAINTS);
        $generisExtension->setConfig('passwords', $passwordConfig);

        $this->getServiceManager()->unregister(PasswordConstraintsServiceInterface::SERVICE_ID);
    }
}
