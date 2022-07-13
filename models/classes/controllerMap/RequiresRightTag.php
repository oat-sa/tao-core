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
*/

namespace oat\tao\model\controllerMap;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;

/**
 * Reflection class for a @requiresRight tag in a Docblock.
 *
 * To be use with the phpDocumentor
 *
 * @author  Joel Bout <joel@taotesting.com>
 */
class RequiresRightTag extends BaseTag
{
    /** @var string */
    protected $parameter = '';

    /** @var string|null */
    protected $rightId = null;

    public function __construct(string $name, ?Description $description = null)
    {
        $this->name        = $name;
        $this->description = $description;
    }

    /**
     * Returns the identifier of the required access right
     *
     * @return string
     */
    public function getRightId()
    {
        return (string) $this->rightId;
    }

    /**
     * Returns the name of the parameter
     *
     * @return string
     */
    public function getParameterName()
    {
        return (string) $this->parameter;
    }

    public static function create(
        string $body,
        string $name = '',
        ?DescriptionFactory $descriptionFactory = null,
        ?TypeContext $context = null
    ) : self {
        Assert::stringNotEmpty($name);
        Assert::notNull($descriptionFactory);

        $description = $body !== '' ? $descriptionFactory->create($body, $context) : null;

        $self = new static($name, $description);

        $parts = preg_split('/\s+/Su', $description, 3);

        if (count($parts) >= 2) {
            $self->parameter = $parts[0];
            $self->rightId = $parts[1];
        }

        return $self;
    }

    public function __toString() : string
    {
        return $this->description ? $this->description->render() : '';
    }
}
