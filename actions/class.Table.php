<?php
/*  
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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * Results Controller provide actions performed from url resolution
 *
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoResults
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 */
abstract class tao_actions_Table extends tao_actions_CommonModule
{

    /**
     * constructor: initialize the service and the default data
     * @return Results
     */
    public function __construct()
    {

        parent::__construct();
    }

    protected function getRootClass()
    {
        throw new common_exception_Error('getRootClass should never be called');
    }

    /*
     * conveniance methods
     */

    protected function getColumns($identifier)
    {
        if (!$this->hasRequestParameter($identifier)) {
            throw new common_Exception('Missing parameter "' . $identifier . '" for getColumns()');
        }
        $columns = array();
        foreach ($this->getRequestParameter($identifier) as $array) {
            $column = tao_models_classes_table_Column::buildColumnFromArray($array);
            if (!is_null($column)) {
                $columns[] = $column;
            }
        }
        return $columns;
    }

    /**
     * get the main class
     * @return core_kernel_classes_Classes
     */
    public function index()
    {
        $filter = $this->getRequestParameter('filter');
        $this->setData('filter', $filter);
        $this->setView('table/index.tpl', 'tao');
    }

    /**
     * Data provider for the table, returns json encoded data according to the parameter
     * @author Bertrand Chevrier, <taosupport@tudor.lu>,
     *
     */
    abstract public function data();


}