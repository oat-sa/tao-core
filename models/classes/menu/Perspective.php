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

class Perspective implements PhpSerializable
{
    const SERIAL_VERSION = 1392821334;
    
    const GROUP_DEFAULT = 'main';
    
    const GROUP_SETTINGS = 'settings';
    
    const GROUP_INVISIBLE = 'invisible';

    private $data = array();
    
    private $sections = array();
    
    /**
     * @param \SimpleXMLElement $node
     * @param $extensionId
     * @return static
     */
    public static function fromSimpleXMLElement(\SimpleXMLElement $node, $extensionId)
    {
        $data = array(
            'id' => (string) $node['id'],
            'visible' => $node['visible'] == 'true',
            'group'       => $node['group']
                ? (string)$node['group']
                : ($node['visible'] == 'true'
                    ? self::GROUP_DEFAULT
                    : self::GROUP_INVISIBLE),
            'name' => (string) $node['name'],
            'js'          => '',
            'description' => (string) $node->description,
            'extension' => $extensionId,
            'level' => (string) $node['level'],
            'icon'        => isset($node->icon) ? Icon::fromSimpleXMLElement($node->icon) : Icon::createLegacyItem('')
        );
        $sections = array();
        foreach ($node->xpath("sections/section") as $sectionNode) {
            $sections[] = Section::fromSimpleXMLElement($sectionNode);
        }
        /*
        if (isset($node->icon)) {
            foreach ($node->icon->attributes() as $key => $attribute) {
                $data['icon'][$key] = (string)$attribute;
            }
        }
        */
        return new static($data, $sections);
    }
    
    /**
     * Generate a Perspective from a legacy ToolbarAction
     * 
     * @param \SimpleXMLElement $node
     * @param $extensionId
     * @return static
     */
    public static function fromLegacyToolbarAction(\SimpleXMLElement $node, $extensionId) {
        $data = array(
            'id'        => (string)$node['id'],
            'extension' => $extensionId,
            'name'		=> (string)$node['title'],
            'level'		=> (int)$node['level'],
            'description'      => empty($text) ? null : $text,
            'js'        => isset($node['js']) ? (string)$node['js'] : null,
            'structure' => isset($node['structure']) ? (string)$node['structure'] : null,
            'group'     => self::GROUP_SETTINGS,
            'icon'        => isset($node['icon']) ? Icon::createLegacyItem($node['icon']) : Icon::createLegacyItem('')
        );
        $children = array();
        if (isset($node['structure'])) {
            $children = array();
            // (string)$node['structure']
        }
        return new static($data, $children); 
    }
    
    /**
     * @param $data
     * @param $sections
     * @param int $version
     */
    public function __construct($data, $sections, $version = self::SERIAL_VERSION)
    {
        $this->data = $data;
        $this->sections = $sections;
    }
    
    /**
     * @return array
     */
    public function getIcon()
    {
        return $this->data['icon'];
    }

    /**
     * @param Section $section
     */
    public function addSection(Section $section)
    {
        $existingKey = false;
        foreach ($this->sections as $key => $existingSection) {
            if ($existingSection->getId() == $section->getId()) {
                $existingKey = $key;
                break;
            }
        }
        if ($existingKey !== false) {
            $this->sections[$existingKey] = $section;
        } else {
            $this->sections[] = $section;
        }
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->data['id'];
    }
    
    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->data['extension'];
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->data['name'];
    }
    
    public function getDescription()
    {
        return $this->data['description'];
    }
    
    public function getGroup()
    {
        return $this->data['group'];
    }

    public function getLevel()
    {
        return $this->data['level'];
    }

    public function isVisible()
    {
        return $this->data['visible'];
    }
    
    public function getSections()
    {
        return $this->sections;
    }
    
    public function getJs()
    {
        return !empty($this->data['js']) ? $this->data['js'] : null;
    }

    public function __toPhpCode()
    {
        return "new ".__CLASS__."("
            .\common_Utils::toPHPVariableString($this->data).','
            .\common_Utils::toPHPVariableString($this->sections).','
            .\common_Utils::toPHPVariableString(self::SERIAL_VERSION)
        .")";
    }
}