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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * Short description of class tao_models_classes_table_PropertyColumn
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 
 */
class tao_models_classes_table_PropertyColumn
    extends tao_models_classes_table_Column
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute property
     *
     * @access public
     * @var Property
     */
    public $property = null;

    // --- OPERATIONS ---

    /**
     * Short description of method fromArray
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  array array
     * @return tao_models_classes_table_PropertyColumn
     */
    protected static function fromArray($array)
    {
        $returnValue = null;

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BF2 begin
        $returnValue = new static(new core_kernel_classes_Property($array['prop']));
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BF2 end

        return $returnValue;
    }

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Property property
     * @return mixed
     */
    public function __construct( core_kernel_classes_Property $property)
    {
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BC8 begin
        $this->property = $property;
        parent::__construct($property->getLabel());
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BC8 end
    }

    /**
     * Short description of method getProperty
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return core_kernel_classes_Property
     */
    public function getProperty()
    {
        $returnValue = null;

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BC2 begin
        $returnValue = $this->property;
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BC2 end

        return $returnValue;
    }

    /**
     * Short description of method getDataProvider
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return tao_models_classes_table_DataProvider
     */
    public function getDataProvider()
    {
        $returnValue = null;

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BD1 begin
        $returnValue = tao_models_classes_table_PropertyDP::singleton();
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BD1 end

        return $returnValue;
    }

    /**
     * Short description of method toArray
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function toArray()
    {
        $returnValue = array();

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BCF begin
        $returnValue = parent::toArray();
        $returnValue['prop'] = $this->property->getUri();
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BCF end

        return (array) $returnValue;
    }

} /* end of class tao_models_classes_table_PropertyColumn */

?>