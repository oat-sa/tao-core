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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\tusUpload\Events;

use oat\oatbox\event\Event;
use Psr\Http\Message\ServerRequestInterface;

abstract class TusEvent implements Event
{
    const EVENT_NAME = __CLASS__;

    /** @var array */
    protected $data;

    /** @var ServerRequestInterface */
    protected $request;

    /**
     * @param array $data
     * @param ServerRequestInterface $request
     *
     */
    public function __construct(array $data, ServerRequestInterface $request)
    {
        $this->data = $data;
        $this->request = $request;
    }

    /**
     * Get file.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get request.
     *
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::EVENT_NAME;
    }
}
