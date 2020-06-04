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
    private $extensionId;

    private $rights_actions = [];
    private $rights_full = [];
    
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        $controller = new self($data['c'], $data['e']);
        $controller->rights_actions = $data['a'];
        $controller->rights_full = $data['f'];
        return $controller;
    }

    public function __construct(string $className, string $extensionId)
    {
        $this->className = $className;
        $this->extensionId = $extensionId;
    }

    public function addFullAccess($role): void
    {
        $this->rights_full[] = $role;
    }

    public function addActionAccess($role, $action): void
    {
        if (!isset($this->rights_actions[$action])) {
            $this->rights_actions[$action] = [];
        }
        $this->rights_actions[$action][] = $role;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getExtensionId(): string
    {
        return $this->extensionId;
    }

    public function getAllowedRoles($action)
    {
        $allowed = $this->rights_full;
        if (isset($this->rights_actions[$action])) {
            $allowed = array_merge($allowed, $this->rights_actions[$action]);
        }
        return $allowed;
    }

    public function jsonSerialize()
    {
        return [
            'c' => $this->className,
            'e' => $this->extensionId,
            'a' => $this->rights_actions,
            'f' => $this->rights_full,
        ];
    }
}
