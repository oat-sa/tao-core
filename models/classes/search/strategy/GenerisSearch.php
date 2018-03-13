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
namespace oat\tao\model\search\strategy;

use core_kernel_classes_Class;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\Search;
use oat\tao\model\search\ResultSet;
use oat\oatbox\service\ConfigurableService;
use oat\generis\model\OntologyAwareTrait;

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
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::query()
     */
    public function query($queryString, $type, $start = 0, $count = 10) {
        $rootClass = $this->getClass($type);
        $results = $rootClass->searchInstances($this->parseQuery($queryString), array(
            'recursive' => true,
            'like'      => true,
            'offset'    => $start,
            'limit'     => $count,
        ));
        $ids = array();
        foreach ($results as $resource) {
            $ids[] = $resource->getUri();
        }

        return new ResultSet($ids, $this->getTotalCount($queryString, $rootClass));
    }

    /**
     * @param $queryString
     * @return array
     */
    private function parseQuery($queryString)
    {
        $query = [];
        $parts = explode('AND', $queryString);
        foreach ($parts as $part) {
            $delimiter = mb_strpos($part, ':');
            $key = \tao_helpers_Uri::decode(trim(substr($part, 0, $delimiter)));
            $key = $key == 'label' ? OntologyRdfs::RDFS_LABEL : $key;
            $propVal = trim(substr($part, $delimiter+1));
            if ($propVal == '*') {
                continue;
            }
            if (!isset($query[$key])) {
                $query[$key] = $propVal;
            } else {
                if (!is_array($query[$key])) {
                    $val = $query[$key];
                    $query[$key] = [];
                    $query[$key][] = $val;
                }
                $query[$key][] = $propVal;
            }
        }
        return $query;
    }

    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::flush()
     */
    public function flush() {
        // no flushing required
    }

    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::addIndexes()
     */
    public function addIndexes(\Traversable $IndexIterator) {
        // no indexation required
        return 0;
    }

    /**
     * Return total count of corresponded instances
     *
     * @param string $queryString
     * @param core_kernel_classes_Class $rootClass
     *
     * @return array
     */
    private function getTotalCount( $queryString, $rootClass = null )
    {
        return $rootClass->countInstances(
            array(
                OntologyRdfs::RDFS_LABEL => $queryString
            ),
            array(
                'recursive' => true,
                'like'      => true,
            )
        );
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::index()
     */
    public function index($document = [])
    {
        // nothing to do
        return true;
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::remove()
     */
    public function remove($resourceId)
    {
        // nothing to do
        return true;
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::supportCustomIndex()
     */
    public function supportCustomIndex()
    {
        return false;
    }
}