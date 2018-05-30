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
namespace oat\tao\model\search;

use oat\oatbox\PhpSerializable;
use oat\tao\model\search\index\IndexIterator;

/**
 * Search interface
 *
 * @author Joel Bout <joel@taotesting.com>
 */
interface Search extends PhpSerializable
{
    const SERVICE_ID = 'tao/search';

    /**
     * Search for instances using a Lucene query
     *
     * @param string $queryString
     * @param string $rootClass
     * @param int $start
     * @param int $count
     * @param int $order
     * @param int $dir
     *
     * @return ResultSet
     */
    public function query( $queryString, $type, $start = 0, $count = 10, $order = 'id', $dir = 'DESC');

    /**
     * Delete all indexes
     */
    public function flush();

    /**
     * (Re)Generate the index for a given document
     * If index is already exist, then it will merge the fields in index with the existing document
     *
     * @param IndexIterator|array $documents
     * @return boolean true if successfully indexed
     */
    public function index($documents);

    /**
     * Remove a resource from the index
     *
     * @param string $resourceId
     * @return boolean true if successfully removed
     */
    public function remove($resourceId);

    /**
     * Whenever or not the current implementation supports
     * custom indexes
     *
     * @return boolean
     */
    public function supportCustomIndex();
}
