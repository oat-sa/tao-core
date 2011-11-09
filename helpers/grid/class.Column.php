<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/grid/class.Column.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 09.11.2011, 16:58:16 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage helpers_grid
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--17d909f0:1336f22bf6e:-8000:0000000000003294-includes begin
// section 127-0-1-1--17d909f0:1336f22bf6e:-8000:0000000000003294-includes end

/* user defined constants */
// section 127-0-1-1--17d909f0:1336f22bf6e:-8000:0000000000003294-constants begin
// section 127-0-1-1--17d909f0:1336f22bf6e:-8000:0000000000003294-constants end

/**
 * Short description of class tao_helpers_grid_Column
 *
 * @access public
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage helpers_grid
 */
class tao_helpers_grid_Column
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute id
     *
     * @access protected
     * @var string
     */
    protected $id = '';

    /**
     * Short description of attribute title
     *
     * @access protected
     * @var string
     */
    protected $title = '';

    /**
     * Short description of attribute type
     *
     * @access protected
     * @var string
     */
    protected $type = '';

    /**
     * Short description of attribute order
     *
     * @access protected
     * @var int
     */
    protected $order = 0;

    /**
     * Short description of attribute adapter
     *
     * @access protected
     * @var Adapter
     */
    protected $adapter = null;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string id
     * @param  string title
     * @return mixed
     */
    public function __construct($id, $title)
    {
        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:0000000000003295 begin
		$this->id = $id;
		$this->title = $title;
        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:0000000000003295 end
    }

    /**
     * Short description of method setType
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string type
     * @return boolean
     */
    public function setType($type)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032C0 begin
		$this->type = $type;
		$returnValue = true;
        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032C0 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getType
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @return string
     */
    public function getType()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032C4 begin
		$returnValue = $this->type;
        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032C4 end

        return (string) $returnValue;
    }

    /**
     * Short description of method setTitle
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string title
     * @return boolean
     */
    public function setTitle($title)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032C6 begin
		$this->title = $title;
		$returnValue = true;
        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032C6 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getTitle
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @return string
     */
    public function getTitle()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032C9 begin
		$returnValue = $this->title;
        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032C9 end

        return (string) $returnValue;
    }

    /**
     * Short description of method getId
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @return string
     */
    public function getId()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032CB begin
		$returnValue = $this->id;
        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032CB end

        return (string) $returnValue;
    }

    /**
     * Short description of method setAdapter
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  Adapter adapter
     * @return boolean
     */
    public function setAdapter( tao_helpers_grid_Cell_Adapter $adapter)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-6c609706:1337d294662:-8000:0000000000003312 begin
		if(!is_null($adapter)){
			$this->adapter = $adapter;
			$returnValue = true;
		}
        // section 127-0-1-1-6c609706:1337d294662:-8000:0000000000003312 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method hasAdapter
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @return boolean
     */
    public function hasAdapter()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-6c609706:1337d294662:-8000:0000000000003318 begin
		$returnValue = ($this->adapter instanceof tao_helpers_grid_Cell_Adapter);
        // section 127-0-1-1-6c609706:1337d294662:-8000:0000000000003318 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getAdapterData
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string rowId
     * @param  mixed cellValue (tao_helpers_grid_Grid, tao_helpers_grid_GridContainer or string)
     * @return mixed
     */
    public function getAdapterData($rowId = '', $cellValue = null)
    {
        $returnValue = null;

        // section 127-0-1-1-6c609706:1337d294662:-8000:000000000000331A begin
		
		if(empty($rowId)){
			
			$returnValue = $this->adapter->getData();
			
		}else{
			
			if($this->hasAdapter()){
				$returnValue = $this->adapter->getValue($rowId, $this->id, $cellValue);
			}

			//allow returning to type "string" or "Grid" only
			if ($returnValue instanceof tao_helpers_grid_Grid) {
				$returnValue = $returnValue->toArray();
			} else if ($returnValue instanceof tao_helpers_grid_GridContainer) {
				$returnValue = $returnValue->toArray();
			} else {
				$returnValue = (string) $returnValue;
			}
			
		}
		
        // section 127-0-1-1-6c609706:1337d294662:-8000:000000000000331A end

        return $returnValue;
    }

} /* end of class tao_helpers_grid_Column */

?>