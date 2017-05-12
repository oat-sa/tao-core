<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 11/05/17
 * Time: 11:54
 */

namespace oat\tao\model\mvc\Application;


use oat\tao\model\mvc\Application\Config\Route;
use oat\tao\model\mvc\Application\Exception\ResolverException;

class Resolution
{

    private $extensionId;

    private $controllerName;

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
    public function __construct($extensionId , $route , $parentRoute)
    {
        $this->extensionId = $extensionId;
        list($controller, $action) = explode('@', $route);
        $this->controllerName = $controller;
        $this->action = $action;
        $this->route  = $parentRoute;
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