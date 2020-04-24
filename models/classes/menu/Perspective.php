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

interface Perspective extends MenuElement, PhpSerializable
{
    const GROUP_DEFAULT = 'main';

    const GROUP_SETTINGS = 'settings';

    const GROUP_INVISIBLE = 'invisible';

    /**
     * @param Section $section
     */
    public function addSection(Section $section);

    /**
     * @return mixed
     */
    public function getName();

    public function getDescription();

    /**
     * @return Icon
     */
    public function getIcon();

    public function getGroup();

    public function getLevel();

    /**
     * @deprecated
     * @return boolean
     */
    public function isVisible();

    public function getChildren();

    public function getBinding();

    public function getUrl();
}
