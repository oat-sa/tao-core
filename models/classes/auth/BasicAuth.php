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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <olexander.zagovorychev@1pt.com>
 */

namespace oat\tao\model\auth;

/**
 * @deprecated
 * Interface BasicAuth
 * @package oat\tao\model\auth
 */
interface BasicAuth
{
    public const CLASS_BASIC_AUTH = 'http://www.tao.lu/Ontologies/TAO.rdf#BasicAuthConsumer';
    public const PROPERTY_LOGIN = 'http://www.tao.lu/Ontologies/TAO.rdf#BasicAuthLogin';
    public const PROPERTY_PASSWORD = 'http://www.tao.lu/Ontologies/TAO.rdf#BasicAuthPassword';
}
