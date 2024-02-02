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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\taskQueue\TaskLog\Decorator;

use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;

class DynamicEntityDecorator extends TaskLogEntityDecorator
{
    public function __construct(
        EntityInterface $entity
    ) {
        parent::__construct($entity);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Add 'hasFile' to the result. Required by our frontend.
     *
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();


        if (isset($result['report']['children'][0]['data']['externalUriLink']['uri'])) {
            $result['redirectUrl'] = $result['report']['children'][0]['data']['externalUriLink']['uri'];
            $result['hasFile'] = true;
        }

        return $result;
    }
}
