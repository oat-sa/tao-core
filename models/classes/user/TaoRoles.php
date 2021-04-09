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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\user;

interface TaoRoles
{
    public const ANONYMOUS = 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole';
    public const BASE_USER = 'http://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole';
    public const BACK_OFFICE = 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole';
    public const SYSTEM_ADMINISTRATOR = 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole';
    public const OPERATIONAL_ADMINISTRATOR = 'http://www.tao.lu/Ontologies/TAO.rdf#OperationalAdministrator';
    public const GLOBAL_MANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole';
    public const DELIVERY = 'http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole';
    public const TAO_MANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole';
    public const REST_PUBLISHER = 'http://www.tao.lu/Ontologies/TAO.rdf#RestPublisher';
    public const LOCK_MANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#LockManagerRole';
    public const PROPERTY_MANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#PropertyManagerRole';

    /** Item Class Roles */
    public const ITEM_CLASS_NAVIGATOR = 'http://www.tao.lu/Ontologies/TAO.rdf#ItemClassNavigatorRole';
    public const ITEM_CLASS_EDITOR = 'http://www.tao.lu/Ontologies/TAO.rdf#ItemClassEditorRole';
    public const ITEM_CLASS_CREATOR = 'http://www.tao.lu/Ontologies/TAO.rdf#ItemClassCreatorRole';
}
