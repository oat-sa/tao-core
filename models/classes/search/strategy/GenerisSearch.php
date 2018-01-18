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
use oat\tao\model\search\Search;
use oat\oatbox\Configurable;
use oat\tao\model\search\ResultSet;
use oat\oatbox\service\ConfigurableService;

/**
 * Simple Search implementation that ignores the indexes
 * and searches over the labels 
 * 
 * @author Joel Bout <joel@taotesting.com>
 */
class GenerisSearch extends ConfigurableService implements Search
{

    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::query()
     */
    public function query($queryString, $rootClass = null, $start = 0, $count = 10) {
        $results = $rootClass->searchInstances(array(
            OntologyRdfs::RDFS_LABEL => $queryString
        ), array(
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
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::fullReIndex()
     */
    public function fullReIndex(\Traversable $resourceTraversable) {
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
     * (Re)Generate the index for a given resource
     *
     * @param core_kernel_classes_Resource $resource
     * @return boolean true if successfully indexed
    */
    public function index(\core_kernel_classes_Resource $resource)
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