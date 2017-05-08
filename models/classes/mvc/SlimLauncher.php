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

use Interop\Container\ContainerInterface;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\mvc\middleware\TaoControllerExecution;
use oat\tao\model\mvc\middleware\TaoErrorHandler;
use oat\tao\model\mvc\middleware\TaoResolver;
use oat\tao\model\mvc\middleware\TaoRestAuthenticate;
use oat\tao\model\mvc\psr7\slimContainerFactory;
use Slim\App;

class SlimLauncher extends ConfigurableService {

    const SERVICE_ID = 'tao/applicationLauncher';

    /**
     * configure slim Container
     * @return ContainerInterface
     */
    protected function configureContainer($container) {

       $containerFactory = $this->getServiceManager()->get(slimContainerFactory::SERVICE_ID);

        $container = $containerFactory->configure($container)->getContainer();

        return $container;

    }

    public function launch() {

        $slimApplication = new App();

        $container = $this->configureContainer($slimApplication->getContainer());

        $container['phpErrorHandler'] = function ($container) {
            return new TaoErrorHandler($container);
        };

        /**
         * @todo use configurable route prefix to support subdirectory install
         */

        $slimApplication->map(['GET', 'POST'] , $this->getOption('prefix') . '[{relativeUrl:.*}]' , TaoResolver::class)
            ->add( TaoRestAuthenticate::class )
            ->add( TaoControllerExecution::class);

        return $slimApplication->run();

    }


}