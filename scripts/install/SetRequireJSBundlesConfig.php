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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;

/**
 * This declares bundling options for requirejs to the extensions configuration
 */
class SetRequireJSBundlesConfig extends InstallAction
{
    public function __invoke($params)
    {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $ext->setConfig('requirejsbundles', array(
            array(
                'name' => 'taobundle_login',
                'path' => ROOT_URL . 'tao/views/dist/loader/login.min',
                'modules' => array(
                    'controller/login',
                ),
            ),
            array(
                'name' => 'taobundle_backoffice',
                'path' => ROOT_URL . 'tao/views/dist/loader/backoffice.min',
                'modules' => array(
                    'controller/backoffice',
                ),
            ),
            array(
                'name' => 'taobundle_app',
                'path' => ROOT_URL . 'tao/views/dist/loader/app.min',
                'modules' => array(
                    'controller/app',
                ),
            ),
            array(
                'name' => 'taobundle',
                'path' => ROOT_URL . 'tao/views/dist/controllers.min',
                'modules' => array(
                    'controller/entry',
                    'controller/Lock/locked',
                    'controller/main',
                    'controller/passwordRecovery',
                    'controller/routes',
                    'controller/settings/extensionManager',
                    'controller/users/add',
                    'controller/users/disable-edit',
                    'controller/users/index',
                ),
            ),
        ));
    }
}
