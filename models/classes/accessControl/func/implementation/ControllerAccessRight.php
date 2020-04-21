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

namespace oat\tao\model\accessControl\func\implementation;

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
class ControllerAccessRight implements \JsonSerializable
{
    private $className;
    private $actions = [];
    private $extension = [];
    
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        $controller = new self($data['c']);
        $controller->actions = $data['a'];
        $controller->extension = $data['e'];
        return $controller;
    }

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function addFullAccess($role): void
    {
        $this->extension[] = $role;
    }

    public function addActionAccess($role, $action): void
    {
        if (!isset($this->actions[$action])) {
            $this->actions[$action] = [];
        }
        $this->actions[$action][] = $role;
        
    }

    public function getClassName(): string
    {
        return $this->className;
    }
    
    public function getAllowedRoles($action)
    {
        $allowed = $this->extension;
        if (isset($this->actions[$action])) {
            $allowed = array_merge($allowed, $this->actions[$action]);
        }
        return $allowed;
    }

    public function jsonSerialize()
    {
        return [
            'c' => $this->className,
            'a' => $this->actions,
            'e' => $this->extension,
        ];
    }


}
