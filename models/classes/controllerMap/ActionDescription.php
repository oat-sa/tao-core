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
 * Copyright (c) 2014-2022 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\controllerMap;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;

/**
 * Description of a Tao Controller
 *
 * @author Joel Bout <joel@taotesting.com>
 * @deprecated use \oat\tao\model\routing\RouteAnnotationService instead
 */
class ActionDescription
{
    /** @var DocBlock|null */
    private static $docBlock;

    /**
     * The method implementing the action
     *
     * @var ReflectionMethod
     */
    private $method;

    /**
     * Create a new lazy parsing action description
     *
     * @param ReflectionMethod $method
     */
    public function __construct(ReflectionMethod $method)
    {
        $this->method = $method;
    }

    /**
     *
     * @return DocBlock
     */
    protected function getDocBlock()
    {
        if (!self::$docBlock instanceof DocBlock) {
            $factory = DocBlockFactory::createInstance();
            $factory->registerTagHandler('requiresRight', RequiresRightTag::class);

            self::$docBlock = $factory->create($this->method);
        }

        return self::$docBlock;
    }

    /**
     * Get the name of the action, which corresponds
     * to the name of the called function
     *
     * @return string
     */
    public function getName()
    {
        return $this->method->getName();
    }

    /**
     * Get a human readable description of what the action does
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getDocBlock()->getSummary();
    }

    /**
     * Returns an array of all rights required to execute the action
     *
     * The array uses the name of the parmeter as key and the value is
     * a string identifying the right
     *
     * @return string[]
     */
    public function getRequiredRights()
    {
        $privileges = [];

        foreach ($this->getDocBlock()->getTagsByName('requiresRight') as $tag) {
            if ($tag instanceof RequiresRightTag) {
                $privileges[$tag->getParameterName()] = $tag->getRightId();
            }
        }

        return $privileges;
    }
}
