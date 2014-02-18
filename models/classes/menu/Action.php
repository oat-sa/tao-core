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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\models\classes\menu;

class Action
{
    private $data;
    
    public function __construct(\SimpleXMLElement $node) {
        $this->data = array(
        	'name' => (string) $node['name'],
            'url' => isset($node['url']) ? (string) $node['url'] : null,
            'js' => (isset($node['js'])) ? (string) $node['js'] : false,
            'context' => (string) $node['context'],
            'reload' => (isset($node['reload'])) ? true : false,
        );
    }
    
    public function getName() {
        return $this->data['name'];
    }
    
    public function getUrl() {
        return $this->data['url'];
    }
    
    public function getJs() {
        return $this->data['js'];
    }
    
    public function getContext() {
        return $this->data['context'];
    }
    
    public function getReload() {
        return $this->data['reload'];
    }
    
    public function getExtensionId() {
        $url = explode('/', trim($this->data['url'], '/'));
        if (count($url) == 3) {
            $ext = (isset($url[0])) ? $url[0] : null;
            return $ext;
        } else {
            return null;
        }
    }
    
    public function hasAccess() {
        $access = true;
        if (!empty($this->data['url'])) {
            $url = explode('/', trim($this->data['url'], '/'));
            if (count($url) == 3) {
                $ext = (isset($url[0])) ? $url[0] : null;
                $module = (isset($url[1])) ? $url[1] : null;
                $action = (isset($url[2])) ? $url[2] : null;
                $access = \tao_models_classes_accessControl_AclProxy::hasAccess($ext, $module, $action);
            }
        }
        return $access;
    }
}