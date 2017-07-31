<?php
/*
 * This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\scripts\install;


use oat\oatbox\extension\InstallAction;
use oat\tao\model\mvc\Application\ApplicationInterface;
use oat\tao\model\mvc\Application\TaoApplication;
use oat\tao\model\routing\LegacyRoute;
use oat\tao\model\routing\NamespaceRoute;

class ExtensionRouteInstallation extends InstallAction
{

    public function __invoke($params) {

        $routes = [];

        if($this->getServiceManager()->has(ApplicationInterface::SERVICE_ID)) {
            $app = $this->getServiceManager()->get(ApplicationInterface::SERVICE_ID);
            $routes = $app->getOption('routes');

        } else {
            $app = new TaoApplication(
                [
                    'templates' =>
                        ['tao' =>
                            [
                            'preProcess' =>
                                [
                                    \oat\tao\model\mvc\middleware\TaoInitUser::class,
                                    \oat\tao\model\mvc\middleware\LoadExtensionConstant::class,
                                    \oat\tao\model\mvc\middleware\TaoRestAuthenticate::class,
                                    \oat\tao\model\mvc\middleware\TaoAuthenticate::class,
                                    \oat\tao\model\mvc\middleware\TaoAssetConfiguration::class,
                                ],
                            'process' =>
                                [
                                    \oat\tao\model\mvc\middleware\TaoControllerExecution::class,

                                ],
                            'postProcess' =>
                                [
                                    \oat\tao\model\mvc\middleware\ControllerRendering::class,
                                ],
                            ],
                        ],
                    'routes'    => [],
                ]
            );

        }

        /**
         * @var \common_ext_Extension $extension
         */
        $extension = $params['ext'];

        /**
         * @var $extRoute array
         */
        $extRoute = $extension->getManifest()->getRoutes();

        foreach ($extRoute as $routeId => $routeData) {

            $route = [
                'ext'          => $extension->getId(),
                'className'    => '',
                'preProcess'   => [],
                'process'      => [],
                'postProcess'  => [],
                'errorHandler' => '',
                'options'      => [],
                'default'      => 'tao',

            ];
            $options = array_key_exists( 'options' , $routeData )? $routeData['options']: [];
            if(array_key_exists('process' , $options)) {
                $route['default'] = $options['process'];
                unset($options['process']);
            }

            if (is_string($routeData)) {

                $route['className'] = NamespaceRoute::class;
                $route['options']   = [NamespaceRoute::OPTION_NAMESPACE => $routeData];

            } else {
                if (!isset($routeData['class']) || !is_subclass_of($routeData['class'], 'oat\tao\model\routing\Route')) {
                    throw new \common_exception_InconsistentData('Invalid route '.$routeId);
                }
                $route['className'] = $routeData['class'];
                $route['options']   = $options;
            }
            $routes[] = $route;
        }
        if (empty($extRoute)) {
            $routes[] = [
                'ext'          => $extension->getId(),
                'className'    => LegacyRoute::class,
                'preProcess'   => [],
                'process'      => [],
                'postProcess'  => [],
                'errorHandler' => '',
                'options'      => [],
                'default'      => 'tao',
            ];
        }
        $app->setOption('routes' , $routes);
        $this->getServiceManager()->register(ApplicationInterface::SERVICE_ID, $app);

    }

}