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

use oat\tao\model\search\Search;
use tao_models_classes_FileSourceService;
use common_Logger;
use oat\oatbox\Configurable;
use oat\tao\model\search\SyntaxException;

/**
 * Zend Lucene Search implementation 
 * 
 * @author Joel Bout <joel@taotesting.com>
 */
class GenerisSearch extends Configurable implements Search
{	
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::query()
     */
    public function query($queryString, $rootClass = null) {
        $results = $rootClass->searchInstances(array(
        	RDFS_LABEL => $queryString
        ), array(
        	'recursive' => true, 'like' => true
        ));
        $ids = array();
        foreach ($results as $resource) {
            $ids[] = $resource->getUri();
        }
        return $ids;
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\search\Search::index()
     */
    public function index(\Traversable $resourceTraversable) {
        // no indexation required
        return 0;
    }

}