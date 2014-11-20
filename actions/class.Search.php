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
 */               

use oat\tao\model\search\SearchService;

/**
 * Controller for indexed searches
 * 
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 */
class tao_actions_Search extends tao_actions_CommonModule {
	
	/**
     * Search parameters endpoints.
     * The response provides parameters to create a datatable.
	 */
	public function searchParams()
	{
        $this->returnJson(array(
	    	'url' => _url('search'),
	        'params' => array(
	            'query' => $this->getRequestParameter('query'),
    	    	'rootNode' => $this->getRequestParameter('rootNode')
    	    ),
	        'filter' => array(),
	        'model' => array(
                RDFS_LABEL => array(
                    'id' => RDFS_LABEL,
                    'label' => __('Label'),
                    'sortable' => false	        	
	            )
            ),
	        'result' => true
	    ));
	}
	
	/**
	 * Search results
     * The search is pagintaed and initiated by the datatable component.
	 */
    public function search()
    {
        $params = $this->getRequestParameter('params');
        $query = $params['query'];
        $class = new core_kernel_classes_Class($params['rootNode']);
        
        common_Logger::i('Search "'.$query.'" in '.$class->getLabel());
        $results = SearchService::getSearchImplementation()->query($query);
        
        $response = new StdClass();
        if(count($results) > 0 ){

            foreach($results as $uri) {
                $instance = new core_kernel_classes_Resource($uri);
                $instanceProperties = array(
                    'id' => $instance->getUri(),
                    RDFS_LABEL => $instance->getLabel() 
                );

                $response->data[] = $instanceProperties; 
            }
        }
		$response->page = 1;
		$response->total = 1;
		$response->records = count($results);

		$this->returnJson($response, 200);
    }

}
