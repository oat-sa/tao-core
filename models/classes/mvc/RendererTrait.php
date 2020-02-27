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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\mvc;

use Renderer;

trait RendererTrait
{
    /**
     * @var Renderer The renderer engine to display view
     */
    protected $renderer;

    public function getRenderer()
    {
        if (!isset($this->renderer)) {
            $this->renderer = new Renderer();
        }
        return $this->renderer;
    }

    /**
     * Helper to set renderer view
     *
     * @param $identifier
     * @return $this
     */
    public function setView($identifier)
    {
        $this->getRenderer()->setTemplate($identifier);
        return $this;
    }


    /**
     * Helper to set renderer data
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function setData($key, $value)
    {
        $this->getRenderer()->setData($key, $value);
        return $this;
    }

    /**
     * Check if the renderer has a view
     *
     * @return bool
     */
    public function hasView()
    {
        return isset($this->renderer) && $this->renderer->hasTemplate();
    }
}
