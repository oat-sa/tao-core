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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\accessControl\func;

use core_kernel_classes_Resource;

/**
 * An access rule gramnting or denying access to a functionality
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class AccessRule
{
    const GRANT = 'grant';
    const DENY = 'deny';
    
    const SCOPE_EXTENSION = 'ext';
    const SCOPE_CONTROLLER = 'mod';
    const SCOPE_ACTION = 'act';
    
    /** @var string */
    private $grantDeny;
    
    /** @var string */
    private $role;
    
    /** @var string */
    private $mask;
    
    /** @var string */
    private $scope;
    /** @var string */
    private $extension;
    /** @var string */
    private $controller;
    /** @var string */
    private $action;
    
    public function __construct($mode, $roleUri, $mask)
    {
        $this->grantDeny = $mode;
        $this->role = $roleUri;
        $this->mask = $mask;
        $this->parseMask();
    }
    
    /**
     * Those the role grant you access?
     * @return bool
     */
    public function isGrant()
    {
        return $this->grantDeny == self::GRANT;
    }
    
    /**
     * Gets the role this rule applies to
     * @return core_kernel_classes_Resource
     */
    public function getRole()
    {
        return new core_kernel_classes_Resource($this->role);
    }

    public function getRoleId()
    {
        return $this->role;
    }

    /**
     * @deprecated please used the preparsed extension, controller, action
     */
    public function getMask()
    {
        return $this->mask;
    }

    public function getScope()
    {
        return $this->scope;
    }
    
    public function getAction(): ?string
    {
        return $this->action;
    }
    
    public function getController(): ?string
    {
        return $this->controller;
    }

    public function getExtensionId(): ?string
    {
        return $this->extension;
    }
    
    private function parseMask(): void
    {
        if (is_string($this->mask)) {
            $this->parseStringMask($this->mask);
        } elseif (is_array($this->mask)) { /// array masks
            $this->parseArrayMask($this->mask);
        } else {
            throw new \common_exception_InconsistentData('Invalid AccessRule mask ' . gettype($this->mask));
        }
    }

    private function parseStringMask(string $mask): void
    {
        $controller = $mask;
        $action = null;
        if (strpos($mask, '@') !== false) {
            [$controller, $action] = explode('@', $mask, 2);
        }
        if (class_exists($controller)) {
            $this->scope = is_null($action) ? self::SCOPE_CONTROLLER : self::SCOPE_ACTION;
            $this->controller = $controller;
            $this->action = $action;
        } else {
            throw new \common_exception_InconsistentData('Invalid AccessRule mask ' . $mask);
        }
    }

    private function parseArrayMask(array $mask): void
    {
        $legacy = $this->checkLegacyMask($mask);
        if (!is_null($legacy)) {
            $this->parseStringMask($legacy);
        } elseif (isset($mask['act'], $mask['mod'], $mask['ext'])) {
            $this->scope = self::SCOPE_ACTION;
            $this->controller = FuncHelper::getClassName($mask['ext'], $mask['mod']);
            $this->action = $mask['act'];
        } elseif (isset($mask['mod'], $mask['ext'])) {
            $this->scope = self::SCOPE_CONTROLLER;
            $this->controller = FuncHelper::getClassName($mask['ext'], $mask['mod']);
        } elseif (isset($mask['ext'])) {
            $this->scope = self::SCOPE_EXTENSION;
            $this->extension = $mask['ext'];
        } else {
            throw new \common_exception_InconsistentData('Invalid AccessRule mask ' . implode(',', array_keys($mask)));
        }
    }

    /**
     * Legacy notation, should not be used, but we still need to support it
     */
    private function checkLegacyMask(array $mask): ?string
    {
        if (isset($mask['controller'])) {
            return $mask['controller'];
        } elseif (isset($mask['act']) && !isset($mask['controller']) && strpos($mask['act'], '@') !== false) {
            return $mask['act'];
        } else {
            return null;
        }
    }
}
