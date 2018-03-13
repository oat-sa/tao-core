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
    const ANONYMOUS = 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole';

    const BASE_USER = 'http://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole';

    const BACK_OFFICE = 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole';

    const SYSTEM_ADMINISTRATOR = 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole';

    const OPERATIONAL_ADMINISTRATOR = 'http://www.tao.lu/Ontologies/TAO.rdf#OperationalAdministrator';

    const GLOBAL_MANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole';

    const DELIVERY = 'http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole';

    const TAO_MANAGER = 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole';

    const REST_PUBLISHER = 'http://www.tao.lu/Ontologies/TAO.rdf#RestPublisher';
}
