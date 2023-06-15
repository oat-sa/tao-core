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
 * Copyright (c) 2017-2018 (original work) Open Assessment Technologies SA;
 *
 */


/**
 *
 * The REST controller to manage RDF classes.
 */
class tao_actions_RestClass extends tao_actions_RestResource
{
    public const CLASS_PARAMETER = 'classUri';

    /**
     * Get all the classes that belong to a subclass.
     * @requiresRight classUri READ
     */
    public function getAll()
    {
        if ($this->isRequestGet()) {
            try {
                $class = $this->getClassParameter();
                $classes = $this->getResourceService()->getAllClasses($class);
                $this->returnSuccess([$classes]);
            } catch (common_Exception $e) {
                $this->returnFailure($e);
            }
        } else {
            $this->returnFailure(new common_exception_MethodNotAllowed(__METHOD__ . ' only accepts GET method'));
        }
    }
}
