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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\tao\model\accessControl\func;

use common_ext_ExtensionsManager;
use oat\oatbox\service\ConfigurableService;

/**
 * Simple function access controll implementation, that builds the access
 * right cache based on the extension definitions.
 * Does not require any update script to maintain
 * @author Joel Bout, <joel@taotesting.com>
 */
class AclModelFactory extends ConfigurableService
{
    public function buildModel(): AclModel
    {
        $aclModel = new AclModel();
        foreach ($this->getExtensionManager()->getInstalledExtensions() as $ext) {
            foreach ($ext->getManifest()->getAclTable() as $tableEntry) {
                $rule = new AccessRule($tableEntry[0], $tableEntry[1], $tableEntry[2]);
                $aclModel->applyRule($rule);
            }
        }
        return $aclModel;
    }

    private function getExtensionManager(): common_ext_ExtensionsManager
    {
        return $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
    }
}
