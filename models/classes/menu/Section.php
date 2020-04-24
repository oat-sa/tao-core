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

interface Section extends MenuElement, PhpSerializable
{

    const POLICY_MERGE = 'merge';
    
    const POLICY_OVERRIDE = 'override';

    public function getUrl();

    public function getName();

    public function getExtensionId();

    public function getController();

    public function getAction();

    /**
     * Policy on how to deal with existing structures
     * Only merge or override are currently supported
     *
     * @return string
     */
    public function getPolicy();

    /**
     * Get the JavaScript binding to run instead of loading the URL
     *
     * @return string|null the binding name or null if none
     */
    public function getBinding();

    /**
     * Is the section disabled ?
     *
     * @return boolean if the section is disabled
     */
    public function getDisabled();

    public function getTrees();

    public function addTree(Tree $tree);

    public function getActions();

    public function addAction(Action $action);

    public function removeAction(Action $action);

    /**
     * @param string $groupId
     * @return array
     */
    public function getActionsByGroup($groupId);
}
