<?php
/**
 *
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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types = 1);

namespace oat\tao\model\migrations;

use oat\oatbox\service\ConfigurableService;
use common_ext_event_ExtensionInstalled as ExtensionInstalled;
use oat\tao\scripts\tools\Migrations;

/**
 * Class MigrationsService
 * @package oat\tao\model\migrations
 */
class MigrationsService extends ConfigurableService
{
    /**
     * Apply all migrations
     * @return \common_report_Report
     */
    public function migrate()
    {
        $migrations = new Migrations();
        $migrations->setServiceLocator($this->getServiceLocator());
        return $migrations->__invoke(['-c', 'migrate']);
    }

    /**
     * Skip extension migrations after installation
     * @param ExtensionInstalled $event
     */
    public function extensionInstalled(ExtensionInstalled $event)
    {
        $migrations = new Migrations();
        $migrations->setServiceLocator($this->getServiceLocator());
        if ($event->getExtension()->getId() === 'tao') {
            //supposedly application initial install and generis just have been installed.
            $migrations->__invoke(['-c', 'init']);
            $migrations->__invoke(['-c', 'add', '-e', 'generis']);
        }
        $migrations->__invoke(['-c', 'add', '-e', $event->getExtension()->getId()]);
    }
}