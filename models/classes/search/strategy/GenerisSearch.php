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
 * Copyright (c) 2014-2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\strategy;

use oat\generis\model\OntologyRdfs;
use oat\tao\model\search\ResultSet;
use oat\oatbox\service\ConfigurableService;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\search\Search;
use Traversable;

/**
 * Simple Search implementation that ignores the indexes
 * and searches over the labels
 *
 * @author Joel Bout <joel@taotesting.com>
 */
class GenerisSearch extends ConfigurableService implements Search
{
    use OntologyAwareTrait;

    /**
     * @inheritDoc
     */
    public function query($queryString, $type, $start = 0, $count = 10, $order = 'id', $dir = 'DESC')
    {
        $rootClass = $this->getClass($type);
        $results = $rootClass->searchInstances(
            [
                OntologyRdfs::RDFS_LABEL => $queryString
            ],
            [
                'recursive' => true,
                'like' => true,
                'offset' => $start,
                'limit' => $count,
            ]
        );

        $ids = [];

        foreach ($results as $resource) {
            $ids[] = [
                'id' => $resource->getUri(),
                'label' => $resource->getLabel()
            ];
        }

        return new ResultSet($ids, $this->getTotalCount($queryString, $rootClass));
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        // no flushing required
    }

    /**
     * @inheritDoc
     */
    public function addIndexes(Traversable $IndexIterator)
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    private function getTotalCount($queryString, $rootClass = null)
    {
        return $rootClass->countInstances(
            [
                OntologyRdfs::RDFS_LABEL => $queryString
            ],
            [
                'recursive' => true,
                'like' => true,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function index($document = [])
    {
        $i = 0;

        foreach ($document as $resource) {
            $i++;
        }

        return $i;
    }

    /**
     * @inheritDoc
     */
    public function remove($resourceId)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function supportCustomIndex()
    {
        return false;
    }
}
