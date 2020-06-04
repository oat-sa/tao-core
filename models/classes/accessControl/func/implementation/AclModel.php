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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\tao\model\accessControl\func\implementation;

use oat\tao\model\accessControl\func\AccessRule;

/**
 * Simple ACL Implementation deciding whenever or not to allow access
 * strictly by the BASEUSER role and a whitelist
 *
 * Not to be used in production, since testtakers cann access the backoffice
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao

 */
class AclModel
{
    private $actionRules = [];
    private $controllerRules = [];
    private $extensionRules = [];

    public function applyRule(AccessRule $rule): void
    {
        switch ($rule->getScope()) {
            case AccessRule::SCOPE_ACTION:
                $this->addAction($rule);
                break;
            case AccessRule::SCOPE_CONTROLLER:
                $this->addController($rule);
                break;
            case AccessRule::SCOPE_EXTENSION:
                $this->addExtension($rule);
                break;
        }
    }

    public function getControllerAcl(string $controllerName, string $extensionId): ControllerAccessRight
    {
        $controller = new ControllerAccessRight($controllerName, $extensionId);
        $this->applyExtensionRules($controller);
        $this->applyControllerRules($controller);
        $this->applyActionRules($controller);
        return $controller;
    }

    private function addAction(AccessRule $rule): void
    {
        $action = $rule->getAction();
        $controller = $rule->getController();
        if (!isset($this->actionRules[$controller])) {
            $this->actionRules[$controller] = [];
        }
        $this->actionRules[$controller][] = [$rule->getRoleId(), $action];
    }

    private function addController(AccessRule $rule): void
    {
        $controller = $rule->getController();
        if (!isset($this->controllerRules[$controller])) {
            $this->controllerRules[$controller] = [];
        }
        $this->controllerRules[$controller][] = $rule->getRoleId();
    }

    private function addExtension(AccessRule $rule): void
    {
        $extensionName = $rule->getExtensionId();
        if (!isset($this->extensionRules[$extensionName])) {
            $this->extensionRules[$extensionName] = [];
        }
        $this->extensionRules[$extensionName][] = $rule->getRoleId();
    }

    private function applyActionRules(ControllerAccessRight $controller): ControllerAccessRight
    {
        if (isset($this->actionRules[$controller->getClassName()])) {
            foreach ($this->actionRules[$controller->getClassName()] as $pair) {
                $controller->addActionAccess($pair[0], $pair[1]);
            }
        }
        return $controller;
    }

    private function applyControllerRules(ControllerAccessRight $controller): ControllerAccessRight
    {
        if (isset($this->controllerRules[$controller->getClassName()])) {
            foreach ($this->controllerRules[$controller->getClassName()] as $roleId) {
                $controller->addFullAccess($roleId);
            }
        }
        return $controller;
    }

    private function applyExtensionRules(ControllerAccessRight $controller): ControllerAccessRight
    {
        $extensionId = $controller->getExtensionId();
        if (isset($this->extensionRules[$extensionId])) {
            foreach ($this->extensionRules[$extensionId] as $roleId) {
                $controller->addFullAccess($roleId);
            }
        }
        return $controller;
    }
}
