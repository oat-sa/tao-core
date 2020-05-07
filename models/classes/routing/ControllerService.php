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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\tao\model\routing;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\http\Controller;
use ReflectionClass;
use ReflectionMethod;

class ControllerService extends ConfigurableService
{
    const SERVICE_ID = 'tao/controllerService';

    /**
     * @param $controllerClass
     * @param string $action
     * @throws RouterException
     */
    private function checkAnnotations($controllerClass, $action = '')
    {
        /** @var RouteAnnotationService $routeAnnotationService */
        $routeAnnotationService = $this->getServiceLocator()->get(RouteAnnotationService::SERVICE_ID);
        // extra layer of the security - to not launch action if denied
        if (!$routeAnnotationService->hasAccess($controllerClass, $action)) {
            $message = $action ? "Unable to run the action '"
                . $action . "' in '" . $controllerClass
                . "', blocked by route annotations." : "Class '$controllerClass' blocked by route annotation";
            throw new RouterException($message);
        }
    }

    /**
     * @param $controllerClass
     * @throws RouterException
     */
    private function checkAbstract($controllerClass)
    {
        try {
            $abstractClass = new ReflectionClass($controllerClass);
        } catch (\ReflectionException $e) {
            throw new RouterException($e->getMessage());
        }
        if ($abstractClass->isAbstract()) {
            throw new RouterException('Attempt to run an action from the Abstract class "' . $controllerClass . '"');
        }
    }

    /**
     * @param string $controllerClass
     * @return mixed
     * @throws RouterException
     */
    public function checkController($controllerClass)
    {
        // abstract class can't be loaded
        $this->checkAbstract($controllerClass);

        // check if blocked by annotations
        $this->checkAnnotations($controllerClass);

        return $controllerClass;
    }

    /**
     * @param $class
     * @param $action
     * @throws RouterException
     */
    private function checkPublic($class, $action)
    {
        try {
            // protected method
            $reflection = new ReflectionMethod($class, $action);
            if (!$reflection->isPublic()) {
                throw new RouterException('The method "' . $action . '" is not public in the class "' . $class . '"');
            }
        } catch (\ReflectionException $e) {
            throw new RouterException($e->getMessage());
        }
    }

    /**
     * @param string $controllerClass
     * @param string $action
     * @throws RouterException
     * @return string
     */
    public function getAction($controllerClass = '', $action = '')
    {
        // method needs to be public
        $this->checkPublic($controllerClass, $action);
        // check if blocked by annotations
        $this->checkAnnotations($controllerClass, $action);

        return $action;
    }
}
