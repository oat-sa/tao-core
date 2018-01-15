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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\search\dataProviders;

/**
 * Interface DataProvider
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 * @package oat\tao\model\search\dataProviders
 */
interface DataProvider
{
    const INDEXES_MAP_OPTION = 'indexesMap';
    const SEARCH_CLASS_OPTION = 'searchClass';
    const LABEL_CLASS_OPTION = 'label';
    const FIELDS_OPTION = 'fields';

    /**
     * @return string
     */
    public function getIndexPrefix();

    /**
     * @return mixed
     */
    public function query($queryString, $rootClass = null, $start = 0, $count = 10);

    /**
     * @param       $id
     * @param array $customBody
     * @return bool
     */
    public function addIndex($id, $customBody = []);

    /**
     * @param $result
     * @return mixed
     */
    public function getResults($result);

    /**
     * @param null $resourceTraversable
     * @return array
     */
    public function prepareDataForIndex($resourceTraversable = null);
}
