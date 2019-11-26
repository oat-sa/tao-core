<?php

declare(strict_types=1);

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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\menu;

use oat\oatbox\PhpSerializable;
use oat\tao\model\entryPoint\Entrypoint as InterfaceEntrypoint;
use tao_models_classes_accessControl_AclProxy;

class Entrypoint implements InterfaceEntrypoint, PhpSerializable
{
    public const SERIAL_VERSION = 1392821334;

    private $data = [];

    public function __construct($data, $version = self::SERIAL_VERSION)
    {
        $this->data = $data;
    }

    public function __toPhpCode()
    {
        return 'new ' . __CLASS__ . '('
            . \common_Utils::toPHPVariableString($this->data) . ','
            . \common_Utils::toPHPVariableString(self::SERIAL_VERSION)
        . ')';
    }

    public static function fromSimpleXMLElement(\SimpleXMLElement $node)
    {
        $replaced = [];
        foreach ($node->xpath('replace') as $replacedNode) {
            $replaced[] = (string) $replacedNode['id'];
        }

        $url = (string) $node['url'];
        [$extension, $controller, $action] = explode('/', trim($url, '/'));

        return new static([
            'id' => (string) $node['id'],
            'title' => (string) $node['title'],
            'label' => (string) $node['label'],
            'desc' => (string) $node->description,
            'url' => $url,
            'extension' => $extension,
            'controller' => $controller,
            'action' => $action,
            'replace' => $replaced,
        ]);
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function getTitle()
    {
        return $this->data['title'];
    }

    public function getLabel()
    {
        return $this->data['label'];
    }

    public function getDescription()
    {
        return $this->data['desc'];
    }

    public function getUrl()
    {
        return _url($this->getAction(), $this->getController(), $this->getExtensionId());
    }

    public function getExtensionId()
    {
        return $this->data['extension'];
    }

    public function getController()
    {
        return $this->data['controller'];
    }

    public function getAction()
    {
        return $this->data['action'];
    }

    public function getReplacedIds()
    {
        return $this->data['replace'];
    }

    public function hasAccess()
    {
        [$ext, $mod, $act] = explode('/', trim($this->data['url'], '/'));
        return tao_models_classes_accessControl_AclProxy::hasAccess($act, $mod, $ext);
    }
}
