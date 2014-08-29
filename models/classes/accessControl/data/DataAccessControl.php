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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
namespace oat\tao\model\accessControl\data;

/**
 * Interface for data based access control
 */
interface DataAccessControl
{
    /**
     * Return the privileges a specified user has on the resources
     * specified by their ids
     * 
     * This function should return an associativ array with the resourceIds
     * as keys an the privilege arrays as values
     * 
     * @todo Pass users as objects that allow for easy role retrieval
     * 
     * @param string $user
     * @param array $resourceIds
     * @return array
     */
    public function getPrivileges($user, array $resourceIds);
}