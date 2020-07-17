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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Service;

class RemoteSourcedListOntology
{
    public const PROPERTY_SOURCE_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#RemoteListSource';
    public const PROPERTY_ITEM_URI_PATH = 'http://www.tao.lu/Ontologies/TAO.rdf#RemoteListItemUriPath';
    public const PROPERTY_ITEM_LABEL_PATH = 'http://www.tao.lu/Ontologies/TAO.rdf#RemoteListItemLabelPath';
    public const PROPERTY_LIST_TYPE = 'http://www.tao.lu/Ontologies/TAO.rdf#ListType';
    public const LIST_TYPE_REMOTE = 'http://www.tao.lu/Ontologies/TAO.rdf#ListRemote';
}
