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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\providers;

use JsonSerializable;
use common_exception_InconsistentData;

/**
 * A class that represents a frontend provider module.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
class ProviderModule implements JsonSerializable
{

    /**
     * @var string $id the provider identifier
     */
    private $id;

    /**
     * @var string $module the provider AMD module
     */
    private $module;

    /**
     * @var string|null $bundle the bundle the provider belongs to
     */
    private $bundle;

    /**
     * @var string $description describes what the providers is doing
     */
    private $description = '';

    /**
     * @var string $name a human readable provider name
     */
    private $name = '';

    /**
     * @var boolean $active if the provider is activated
     */
    private $active = true;

    /**
     * @var string $category the provider belongs to a category, to group them
     */
    private $category;

    /**
     * @var string[] $tags tags to add labels to providers
     */
    private $tags = [];


    /**
     * Creates a frontend provider
     * @param string $id the provider identifier
     * @param string $module the provider AMD module
     * @param string $category the category the provider belongs to
     * @param array $data optional other properties
     * @throws common_exception_InconsistentData
     */
    public function __construct($id, $module, $category, $data = [])
    {

        self::validateRequiredData($id, $module, $category);

        $this->id = (string)$id;
        $this->module = (string)$module;
        $this->category = (string)$category;

        if (isset($data['bundle'])) {
            $this->bundle = (string)$data['bundle'];
        }
        if (isset($data['description'])) {
            $this->description = (string)$data['description'];
        }
        if (isset($data['name'])) {
            $this->name = (string)$data['name'];
        }
        if (isset($data['active'])) {
            $this->active = (boolean)$data['active'];
        }
        if (isset($data['tags'])) {
            $this->tags = (array)$data['tags'];
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getBundle()
    {
        return $this->bundle;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = (boolean)$active;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function hasTag($tag)
    {
        return in_array($this->tags, $tag);
    }

    /**
     * @see JsonSerializable::jsonSerialize
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convenient method to convert the members to an assoc array
     * @return array the data
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'module' => $this->module,
            'bundle' => $this->bundle,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'active' => $this->active,
            'tags' => $this->tags
        ];
    }

    /**
     * Creates a test provider from an assoc array
     * @param array $data
     * @return ProviderModule the new instance
     * @throws common_exception_InconsistentData
     */
    public static function fromArray(array $data)
    {

        if (!isset($data['id']) || !isset($data['module']) || !isset($data['category'])) {
            throw new common_exception_InconsistentData('The provider requires an id, a module and a category');
        }
        return new static($data['id'], $data['module'], $data['category'], $data);
    }

    /**
     * Validates required data to construct a provider
     * @param mixed $id
     * @param mixed $module
     * @param mixed $category
     * @return boolean true
     * @throws common_exception_InconsistentData
     */
    private static function validateRequiredData($id, $module, $category)
    {

        if (!is_string($id) || empty($id)) {
            throw new common_exception_InconsistentData('The provider needs an id');
        }
        if (!is_string($module) || empty($module)) {
            throw new common_exception_InconsistentData('The provider needs a module');
        }
        if (!is_string($category) || empty($category)) {
            throw new common_exception_InconsistentData('The provider needs a category');
        }

        return true;
    }
}
