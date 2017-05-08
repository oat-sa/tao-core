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

namespace oat\tao\model\mvc\psr7;


use oat\oatbox\service\ConfigurableService;
use oat\tao\model\mvc\middleware\TaoErrorHandler;
use Slim\Container;

class slimContainerFactory extends ConfigurableService
{

    const SERVICE_ID = 'tao/slimContainer';

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container|null $container
     * @return $this
     */
    public function configure(Container $container = null) {

        if(is_null($container)) {
            $container = new Container();
        }

        $options = $this->getOptions();
        $this->container = $container;

        $serviceManager = $this->getServiceManager();
        $this->container['taoService'] = function() use ($serviceManager) {
            return $serviceManager;
        };

        foreach ($options as $alias => $className) {
            if(class_exists($className)) {

                $object = new $className;
                $this->container[$alias] = function() use ($object) {
                    return $object;
                };

            }
        }

        return $this;

    }

    public function getContainer() {

        return $this->container;

    }
}