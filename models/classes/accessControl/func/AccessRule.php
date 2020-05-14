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
    
    /** @var array */
    private $component;
    
    public function __construct($mode, $roleUri, $mask)
    {
        $this->grantDeny = $mode;
        $this->role = $roleUri;
        $this->mask = $mask;
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
     * Returns the filter of the rule
     * @return array
     */
    public function getMask()
    {
        return $this->mask;
    }

    public function getScope()
    {
        switch (count($this->getComponents())) {
            case 1: return self::SCOPE_EXTENSION;
            case 2: return self::SCOPE_CONTROLLER;
            case 3: return self::SCOPE_ACTION;
            default:
                throw new \Exception('Invalid rule mask');
        }
    }
    
    public function getAction(): ?string
    {
        $components = $this->getComponents();
        return count($components) == 3 ? $components[2] : null;
    }
    
    public function getController(): ?string
    {
        $components = $this->getComponents();
        return count($components) >= 2
            ? FuncHelper::getClassName($components[0], $components[1])
            : null;
    }

    public function getExtensionId(): string
    {
        return $this->getComponents()[0];
    }
    
    /**
     * Get ACL components represented by the mask
     * @return string[] tao ACL components
     */
    protected function getComponents(): array
    {
        // string masks
        if (is_string($this->mask)) {
            return $this->getComponentsFromString($this->mask);
        } elseif (is_array($this->mask)) { /// array masks
            return $this->getComponentsFromArray($this->mask);
        } else {
            \common_Logger::w('Uninterpretable filtertype ' . gettype($this->mask));
            return [];
        }
    }

    protected function getComponentsFromString(string $mask): array
    {
        if (strpos($this->mask, '@') !== false) {
            [$controller, $action] = explode('@', $this->mask, 2);
        } else {
            $controller = $this->mask;
            $action = null;
        }
        if (class_exists($controller)) {
            $extension = FuncHelper::getExtensionFromController($controller);
            $shortName = strpos($controller, '\\') !== false
                ? substr($controller, strrpos($controller, '\\') + 1)
                : substr($controller, strrpos($controller, '_') + 1);
            if (is_null($action)) {
                // grant controller
                return [$extension, $shortName];
            }
            // grant action
            return [$extension, $shortName, $action];
        }
        \common_Logger::w('Unknown controller ' . $controller);
        return [];
    }

    protected function getComponentsFromArray(array $mask): array
    {
        if (isset($this->mask['act'], $this->mask['mod'], $this->mask['ext'])) {
            return [$this->mask['ext'], $this->mask['mod'], $this->mask['act']];
        }
        if (isset($this->mask['mod'], $this->mask['ext'])) {
            return [$this->mask['ext'], $this->mask['mod']];
        }
        if (isset($this->mask['ext'])) {
            return [$this->mask['ext']];
        }
        if (isset($this->mask['controller'])) {
            return $this->getComponentsFromString($this->mask['controller']);
        }
        if (isset($this->mask['act']) && strpos($this->mask['act'], '@') !== false) {
            return $this->getComponentsFromString($this->mask['act']);
        }
        \common_Logger::w('Uninterpretable filter array: '.implode(',', array_keys($mask)));
        return [];
    }
}
