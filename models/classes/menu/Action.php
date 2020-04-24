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
use oat\oatbox\service\ServiceManagerAwareInterface;

interface Action extends PhpSerializable, ServiceManagerAwareInterface
{

    public function getName();

    public function getId();

    public function getDisplay();

    public function getUrl();

    public function getRelativeUrl();

    public function getBinding();

    public function getContext();

    public function getReload();

    public function getDisabled();

    public function getGroup();

    /**
     * @return Icon
     */
    public function getIcon();

    /**
     * Is the action available for multiple resources
     * @return bool
     */
    public function isMultiple();

    /**
     * Get the extension id from the action's URL.
     *
     * @return string the extension id
     */
    public function getExtensionId();

    public function getController();

    public function getAction();


    /**
     *  Check whether the current is allowed to see this action (against ACL).
     *  @deprecated Wrong layer. Should be called at the level of the controller
     *  @return bool true if access is granted
     */
    public function hasAccess();
}
