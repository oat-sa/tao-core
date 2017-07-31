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

namespace oat\tao\model\mvc\Application;


use oat\tao\model\mvc\Application\Config\Route;
use oat\tao\model\mvc\Application\Exception\ResolverException;

class Resolution
{

    private $extensionId;

    private $controllerName;

    private $moduleName;

    private $action;

    private $controller;

    /**
     * @var Route
     */
    private $route;

    /**
     * Resolution constructor.
     * @param string $extensionId
     * @param string $route
     * @param $parentRoute
     */
    public function __construct($extensionId , $route , $relativeUrl , $parentRoute)
    {
        $this->extensionId = $extensionId;
        list($controller, $action) = explode('@', $route);
        $this->controllerName = $controller;
        $this->moduleName  = $this->extractModuleName($relativeUrl);
        $this->action = $action;
        $this->route  = $parentRoute;
    }

    private function extractModuleName($relativeUrl) {
        $defaultModuleName = 'Main';
        $module = null;

        if (defined('DEFAULT_MODULE_NAME')){
            $defaultModuleName = DEFAULT_MODULE_NAME;
        }

        $relPath		= ltrim($relativeUrl, '/');
        $tab = explode('/', $relPath);

        if (count($tab) > 0) {
            $module		= isset($tab[1]) && !empty($tab[1]) ? $tab[1] : $defaultModuleName;
        }

        return $module;
    }

    /**
     * @return string
     */
    public function getModuleName() {
        return $this->moduleName;
    }

    /**
     * @return string
     */
    public function getExtensionId() {
        return $this->extensionId;
    }

    /**
     * @return string
     */
    public function getControllerClass() {
        return $this->controllerName;
    }

    /**
     * @return string
     */
    public function getMethodName() {
        return $this->action;
    }

    /**
     * @return Route
     */
    public function getRoute() {
        return $this->route;
    }

    /**
     * @return \tao_actions_CommonModule
     */
    public function getController() {
        if(is_null($this->controller)) {
            $className = $this->controllerName;

            if(!class_exists($className)) {
                throw new ResolverException('controller ' . $this->controllerName . ' not found');
            }
            $this->controller = new $className();

        }
        return $this->controller;
    }

}