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

class Perspective extends MenuElement implements PhpSerializable
{
    public const GROUP_DEFAULT = 'main';

    public const GROUP_SETTINGS = 'settings';

    public const GROUP_INVISIBLE = 'invisible';

    private $data = [];

    private $children = [];

    /**
     * @param \SimpleXMLElement $node
     * @param $structureExtensionId
     * @return static
     */
    public static function fromSimpleXMLElement(\SimpleXMLElement $node, $structureExtensionId)
    {
        $data = [
            'id'       => (string) $node['id'],
            'group'    => $node['group']
                ? (string)$node['group']
                : ($node['visible'] == 'true'
                    ? self::GROUP_DEFAULT
                    : self::GROUP_INVISIBLE),
            'name'      => (string) $node['name'],
            'binding'     => isset($node['binding']) ? (string)$node['binding'] : null,
            'description' => (string) $node->description,
            'extension' => $structureExtensionId,
            'level'     => (string) $node['level'],
            'icon'      => isset($node->icon) ? Icon::fromSimpleXMLElement($node->icon, $structureExtensionId) : null
        ];
        $sections = [];
        foreach ($node->xpath("sections/section") as $sectionNode) {
            $sections[] = Section::fromSimpleXMLElement($sectionNode, $structureExtensionId);
        }
        return new static($data, $sections);
    }

    /**
     * Generate a Perspective from a legacy ToolbarAction
     *
     * @param \SimpleXMLElement $node
     * @param $structureExtensionId
     * @return static
     */
    public static function fromLegacyToolbarAction(\SimpleXMLElement $node, $structureExtensionId)
    {
        $data = [
            'id' => (string)$node['id'],
            'extension' => $structureExtensionId,
            'name' => (string)$node['title'],
            'level' => (int)$node['level'],
            'description' => empty($text) ? null : $text,
            'binding' => isset($node['binding'])
                ? (string)$node['binding']
                : (isset($node['js']) ? (string)$node['js'] : null),
            'structure' => isset($node['structure']) ? (string)$node['structure'] : null,
            'group' => self::GROUP_SETTINGS,
            'icon' => isset($node['icon'])
                ? Icon::fromArray(['id' => (string)$node['icon']], $structureExtensionId)
                : null
        ];
        $children = [];
        if (isset($node['structure'])) {
            $children = [];
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
        parent::__construct($data['id'], $version);
        $this->data = $data;
        $this->children = $sections;
    }

    /**
     * @param Section $section
     */
    public function addSection(Section $section)
    {
        $existingKey = false;
        foreach ($this->children as $key => $existingSection) {
            if ($existingSection->getId() == $section->getId()) {
                $existingKey = $key;
                break;
            }
        }
        if ($existingKey !== false) {
            switch ($section->getPolicy()) {
                case Section::POLICY_MERGE:
                    $currentSection = $this->children[$existingKey];
                    foreach ($section->getTrees() as $tree) {
                        $currentSection->addTree($tree);
                    }
                    /** @var Action $action */
                    foreach ($section->getActions() as $action) {
                        /** @var Action $currentAction */
                        foreach ($currentSection->getActions() as $currentAction) {
                            if ($currentAction->getId() == $action->getId()) {
                                $currentSection->removeAction($currentAction);
                                break;
                            }
                        }
                        $currentSection->addAction($action);
                    }
                    break;
                case Section::POLICY_OVERRIDE:
                    $this->children[$existingKey] = $section;
                    break;
                default:
                    throw new \common_exception_Error();
            }
        } else {
            $this->children[] = $section;
        }
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

    /**
     * @return Icon
     */
    public function getIcon()
    {
        return $this->data['icon'];
    }

    public function getGroup()
    {
        return $this->data['group'];
    }

    public function getLevel()
    {
        return $this->data['level'];
    }

    /**
     * @deprecated
     * @return boolean
     */
    public function isVisible()
    {
        return $this->getGroup() == self::GROUP_INVISIBLE;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getBinding()
    {
        return $this->data['binding'];
    }

    public function getUrl()
    {
        return _url('index', null, null, ['structure' => $this->getId(), 'ext' => $this->getExtension()]);
    }

    public function __toPhpCode()
    {
        return "new " . __CLASS__ . "("
            . \common_Utils::toPHPVariableString($this->data) . ','
            . \common_Utils::toPHPVariableString($this->children) . ','
            . \common_Utils::toPHPVariableString(self::SERIAL_VERSION)
        . ")";
    }
}
