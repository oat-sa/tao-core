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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\tao\scripts\install;

/**
 * Description of ServiceInjectorInstaller
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class ServiceInjectorInstaller extends \common_ext_action_InstallAction {

    public function __invoke($params) {

        $this->setServiceInjectorConfig(
                [
                    \oat\oatbox\service\factory\ZendServiceManager::class =>
                    [
                        'shared' =>
                        [
                            'tao.routing.resolver' => false,
                            'tao.routing.action' => false,
                            'tao.action.resolver' => false,
                        ],
                        'invokables' =>
                        [
                            'tao.routing.controller' => '\\oat\\tao\\model\\routing\\TaoFrontController',
                            'tao.routing.resolver' => '\\oat\\tao\\model\\routing\\Resolver',
                            'tao.routing.action' => '\\oat\\tao\\model\\routing\\ActionEnforcer',
                            'tao.action.resolver' => '\\oat\\tao\\model\\routing\\ActionResolver',
                            'tao.routing.flow' => '\\oat\\tao\\model\\routing\\FlowController',
                            'tao.routing.cli' => '\\oat\\tao\\model\\routing\\CliController',
                        ],
                        'abstract_factories' =>
                        [
                            '\\oat\\tao\\model\\routing\\ControllerFactory',
                        ],
                    ],
                ]
        );
    }

}
