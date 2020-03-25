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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

/**
 * Short description of class tao_helpers_grid_Cell_Adapter
 *
 * @abstract
 * @access public
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao

 */
abstract class tao_helpers_grid_Cell_Adapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute data
     *
     * @access protected
     * @var array
     */
    protected $data = [];

    /**
     * Short description of attribute options
     *
     * @access protected
     * @var array
     */
    protected $options = [];

    /**
     * Short description of attribute excludedProperties
     *
     * @access public
     * @var array
     */
    public $excludedProperties = [];

    // --- OPERATIONS ---

    /**
     * Short description of method getValue
     *
     * @abstract
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string rowId
     * @param  string columnId
     * @param  string data
     * @return mixed
     */
    abstract public function getValue($rowId, $columnId, $data = null);

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = [])
    {
        
        $this->options = $options;
        $this->excludedProperties = (is_array($this->options) && isset($this->options['excludedProperties'])) ? $this->options['excludedProperties'] : [];
    }

    /**
     * Short description of method getData
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @return array
     */
    public function getData()
    {
        $returnValue = [];

        
        $returnValue = $this->data;
        

        return (array) $returnValue;
    }
} /* end of abstract class tao_helpers_grid_Cell_Adapter */
