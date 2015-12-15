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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\requiredAction\implementation;

/**
 * Class TimeRule
 * @author Aleh Hutnilau <hutnikau@1pt.com>
 */
class CodeOfConductRule extends TimeRule
{

    private $roles = [
        'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole',
        'http://www.tao.lu/Ontologies/TAOProctor.rdf#ProctorRole'
    ];

    public function check()
    {
        $user = $this->getUser();
        $userRoles = $user->getRoles();
        $result = count(array_intersect($userRoles, $this->roles)) > 0;
        $result = $result && parent::check();
        return $result;
    }
}