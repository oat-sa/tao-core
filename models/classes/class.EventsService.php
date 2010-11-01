<?php

error_reporting(E_ALL);

/**
 * The event service enables you to manage events catching and tracing.
 * It provides methods to read files describing which event to catch 
 * and methods to print out the events of an item execution into a log file.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_models_classes_Service
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/models/classes/class.Service.php');

/* user defined includes */
// section 127-0-1-1--400678db:12c07cdfee6:-8000:000000000000273F-includes begin
// section 127-0-1-1--400678db:12c07cdfee6:-8000:000000000000273F-includes end

/* user defined constants */
// section 127-0-1-1--400678db:12c07cdfee6:-8000:000000000000273F-constants begin
// section 127-0-1-1--400678db:12c07cdfee6:-8000:000000000000273F-constants end

/**
 * The event service enables you to manage events catching and tracing.
 * It provides methods to read files describing which event to catch 
 * and methods to print out the events of an item execution into a log file.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_EventsService
    extends tao_models_classes_Service
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Retrieve the list of events to catch from a XML file.
     * The XML format used describes events that can be catched either on client
     * on server side.
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string file
     * @param  string side
     * @return array
     */
    public function getEventList($file, $side = 'client')
    {
        $returnValue = array();

        // section 127-0-1-1--400678db:12c07cdfee6:-8000:0000000000002741 begin
        
    	$knownSides = array('client', 'server');
        if(!in_array($side, $knownSides)){
        	throw new Exception("Unkown side {$side} to catch events. Expected: [".implode(',', $knownSides)."] ");
        }
        if(!file_exists($file)){
        	throw new Exception("Unable to open file $file");
        }
        
        $returnValue = array('type' => '', 'list' => array());
        
    	try
		{
			$event_dom = new DomDocument();
			$event_dom->load($file);

			$event_xpath = new DOMXPath($event_dom);
			$filter = $event_xpath->query("//filter[@where='$side']");
			if(count($filter->item(0)) == 1){
				$matches = array();
				preg_match_all('/([^\[|,]+)(\[([^\]]+)\]|,)*/', $filter->item(0)->nodeValue, $matches);
				
				if(isset($matches[1])){
					$event_array = array();
					foreach($matches[1] as $key => $event){
						$event_array[$event] = array();
						foreach(explode(',', $matches[3][$key]) as $attribute){
							if(!empty($attribute))
								array_push($event_array[$event], $attribute);
						}
					}
		
					$returnValue = array(
							'type' => $filter->item(0)->getAttribute('type'),
							'list' => $event_array
						);
				}
			}
		}
		catch(DomException $de){ 
			print  $de; 
		}
        
        // section 127-0-1-1--400678db:12c07cdfee6:-8000:0000000000002741 end

        return (array) $returnValue;
    }

    /**
     * Print out the events in parameters into a formated events log file.
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array events
     * @return boolean
     */
    public function traceEvent($events)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--400678db:12c07cdfee6:-8000:0000000000002744 begin
        // section 127-0-1-1--400678db:12c07cdfee6:-8000:0000000000002744 end

        return (bool) $returnValue;
    }

} /* end of class tao_models_classes_EventsService */

?>