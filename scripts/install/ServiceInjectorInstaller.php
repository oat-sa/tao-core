<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
