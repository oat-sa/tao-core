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

namespace oat\tao\model\mvc;

use oat\oatbox\service\ServiceManager;
use oat\tao\model\mvc\middleware\TaoAuthenticate;
use oat\tao\model\mvc\middleware\TaoControllerExecution;
use oat\tao\model\mvc\middleware\TaoResolver;
use oat\tao\model\mvc\psr7\Context;
use oat\tao\model\mvc\psr7\Resolver;
use Slim\App;
use Slim\Container;

class SlimLauncher {

    protected function configureContainer() {
        $container = new Container();

        $container['resolver'] = function () {
            return new Resolver();
        };

        $container['context'] = function () {
            return new Context();
        };

        $container['taoService'] = function () {
            return ServiceManager::getServiceManager();
        };

        return $container;

    }

    public function launch() {
        $container = $this->configureContainer();

        try {
            $slimApplication = new App($container);

            $slimApplication->map(['GET', 'POST'] , '/[{ext}[/{mod}[/{action}]]]' , TaoResolver::class)
                ->add( TaoAuthenticate::class )
                ->add( TaoControllerExecution::class);

            return $slimApplication->run();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }


}