<?php
/*  
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
  * Copyright (c) 2013 Open Assessment Technologies S.A. *
 */
/**
 * Implements Lock using a simple property in the ontology for the lock storage
 *
 **/


class tao_helpers_lock_LockData{
    private $resource; //the resource being locked core_kernel_classe_Resource
    private $owner; //the owner of the lock core_kernel_classe_Resource
    private $epoch; //the epoch when the lock was set up

    public function __construct(core_kernel_classes_Resource $resource, core_kernel_classes_Resource $owner, $epoch) {
        $this->resource = $resource;
        $this->owner = $owner;
        $this->epoch = $epoch;
    }
    public function getResource() {
        return $this->resource;
    }
    public function getEpoch(){
        return $this->epoch;
    }
    public function getOwner(){
        return $this->owner;
    }

 
}
?>