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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\taskQueue\TaskLog\Decorator;

use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\taoBackOffice\model\routing\ResourceUrlBuilder;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class RedirectUrlEntityDecorator extends TaskLogEntityDecorator
{
    use OntologyAwareTrait;

    /**
     * @var ResourceUrlBuilder
     */
    private $urlBuilder;

    public function __construct(EntityInterface $entity, ResourceUrlBuilder $urlBuilder)
    {
        parent::__construct($entity);

        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Add 'redirectUrl' to the result.
     *
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        $uri = $this->getResourceUriFromReport();

        $result['redirectUrl'] = $uri
            ? $this->urlBuilder->buildUrl($this->getResource($uri))
            : '';

        return $result;
    }
}