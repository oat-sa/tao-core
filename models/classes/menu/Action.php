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

namespace oat\tao\model\menu;

use oat\oatbox\PhpSerializable;
use tao_models_classes_accessControl_AclProxy;

class Action implements PhpSerializable
{
    const SERIAL_VERSION = 1392821334;

    const GROUP_DEFAULT = 'tree';
    
    public static function fromSimpleXMLElement(\SimpleXMLElement $node) {
        $data = array(
            'name'      => (string) $node['name'],
            'url'       => isset($node['url']) ? (string) $node['url'] : '#',
            'binding'   => isset($node['binding']) ? (string) $node['binding'] : isset($node['js']) ? (string) $node['js'] : 'load',
            'context'   => (string) $node['context'],
            'reload'    => isset($node['reload']) ? true : false,
            'group'     => isset($node['group']) ? (string) $node['group'] : self::GROUP_DEFAULT
        );
        if(isset($node->icon)){
            $data['icon'] = Icon::fromSimpleXMLElement($node->icon);
        }

        return new static($data);
    }
    
    public function __construct($data, $version = self::SERIAL_VERSION) {
        $this->data = $data;
        if(!isset($this->data['icon'])){
            $this->data['icon'] = $this->inferLegacyIcon($data);
        }
    }
    
    public function getName() {
        return $this->data['name'];
    }

    public function getDisplay() {
        return $this->data['display'];
    }
    
    public function getUrl() {
        return $this->data['url'];
    }
    
    public function getBinding() {
        return $this->data['binding'];
    }
    
    public function getContext() {
        return $this->data['context'];
    }
    
    public function getReload() {
        return $this->data['reload'];
    }

    public function getGroup() {
        return $this->data['group'];
    }

    /**
     * @return Icon
     */
    public function getIcon() {
        return $this->data['icon'];
    }

    /**
     * Get the extension id from the action's URL.
     *
     * @param string $url (optional) the url to extract the extension id, use $this->data['url'] if not set.
     * @return string the extension id
     */
    public function getExtensionId($url = null) {
        if(is_null($url)){
            $url = $this->data['url'];
        }
        $urlParts = explode('/', trim($url, '/'));
        if (count($urlParts) == 3) {
            $ext = (isset($urlParts[0])) ? $urlParts[0] : null;
            return $ext;
        } else {
            return null;
        }
    }

    /**
     * Try to get the action's icon the old way. 
     * I/O impact (file_exists) is limited as the results can be serialized.
     *
     * @return Icon the icon with the src property set to the icon URL.
     */
    private function inferLegacyIcon(){
        $ext = $this->getExtensionId();
        $name = strtolower(\tao_helpers_Display::textCleaner($this->data['name']));
        $file = $ext . '/views/img/actions/' . $name . '.png';
        $src = 'actions/' . $name . '.png';
        if(file_exists(ROOT_PATH . $file)) {
            return Icon::fromArray(array('src' => $src), $ext);
        }
        else if (file_exists(ROOT_PATH . 'tao/views/img/actions/' . $name . '.png')){
            return Icon::fromArray(array('src' => $src), 'tao');
        }
        else {
            return Icon::fromArray(array('src' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAAAAAA6fptVAAAAAnRSTlMA/1uRIrUAAAAKSURBVHjaY/gPAAEBAQAcsIyZAAAAAElFTkSuQmCC'), 'tao');
        }
    }
   
    /**
     *  Check whether the current is allowed to see this action (against ACL).
     *
     *  @return bool true if access is granted
     */ 
    public function hasAccess() {
        $access = true;
        if (!empty($this->data['url'])) {
            $url = explode('/', trim($this->data['url'], '/'));
            if (count($url) == 3) {
                $ext = (isset($url[0])) ? $url[0] : null;
                $module = (isset($url[1])) ? $url[1] : null;
                $action = (isset($url[2])) ? $url[2] : null;
                $access = tao_models_classes_accessControl_AclProxy::hasAccess($action, $module, $ext);
            }
        }
        return $access;
    }
    
    public function __toPhpCode() {
        return "new ".__CLASS__."("
            .\common_Utils::toPHPVariableString($this->data).','
            .\common_Utils::toPHPVariableString(self::SERIAL_VERSION)
        .")";
    }
}
