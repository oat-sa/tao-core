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
 * Short description of class tao_models_classes_LanguageService
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 
 */
class tao_models_classes_LanguageService
    extends tao_models_classes_GenerisService
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method createLanguage
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string code
     * @return core_kernel_classes_Resource
     */
    public function createLanguage($code)
    {
        $returnValue = null;

        // section 10-30-1--78--4ba5e2df:13b03a2f5f0:-8000:0000000000003C14 begin
        throw new common_exception_Error(__METHOD__.' not yet implemented in '.__CLASS__);
        // section 10-30-1--78--4ba5e2df:13b03a2f5f0:-8000:0000000000003C14 end

        return $returnValue;
    }

    /**
     * Short description of method getLanguageByCode
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string code
     * @return core_kernel_classes_Resource
     */
    public function getLanguageByCode($code)
    {
        $returnValue = null;

        // section 10-30-1--78--4ba5e2df:13b03a2f5f0:-8000:0000000000003C17 begin
        $langClass = new core_kernel_classes_Class(CLASS_LANGUAGES);
	    $langs = $langClass->searchInstances(array(
	    	RDF_VALUE => $code
	    ), array(
	    	'like' => false
	    ));
	    if (count($langs) == 1) {
	    	$returnValue = current($langs);
	    } else {
	    	common_Logger::w('Could not find language with code '.$code);
	    }
        // section 10-30-1--78--4ba5e2df:13b03a2f5f0:-8000:0000000000003C17 end

        return $returnValue;
    }

    /**
     * Short description of method getCode
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource language
     * @return string
     */
    public function getCode( core_kernel_classes_Resource $language)
    {
        $returnValue = (string) '';

        // section 10-30-1--78--4ba5e2df:13b03a2f5f0:-8000:0000000000003C1A begin
	    $valueProperty = new core_kernel_classes_Property(RDF_VALUE);
        $returnValue = $language->getUniquePropertyValue($valueProperty);
        // section 10-30-1--78--4ba5e2df:13b03a2f5f0:-8000:0000000000003C1A end

        return (string) $returnValue;
    }

    /**
     * Short description of method getAvailableLanguagesByUsage
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource usage
     * @return array
     */
    public function getAvailableLanguagesByUsage( core_kernel_classes_Resource $usage)
    {
        $returnValue = array();

        // section 10-30-1--78--4ba5e2df:13b03a2f5f0:-8000:0000000000003C1D begin
    	$langClass = new core_kernel_classes_Class(CLASS_LANGUAGES);
	    $returnValue = $langClass->searchInstances(array(
	    	PROPERTY_LANGUAGE_USAGES => $usage->getUri()
	    ), array(
	    	'like' => false
	    ));
        // section 10-30-1--78--4ba5e2df:13b03a2f5f0:-8000:0000000000003C1D end

        return (array) $returnValue;
    }

    /**
     * Short description of method getDefaultLanguageByUsage
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource usage
     * @return core_kernel_classes_Resource
     */
    public function getDefaultLanguageByUsage( core_kernel_classes_Resource $usage)
    {
        $returnValue = null;

        // section 10-30-1--78--4ba5e2df:13b03a2f5f0:-8000:0000000000003C20 begin
        throw new common_exception_Error(__METHOD__.' not yet implemented in '.__CLASS__);
        // section 10-30-1--78--4ba5e2df:13b03a2f5f0:-8000:0000000000003C20 end

        return $returnValue;
    }

} /* end of class tao_models_classes_LanguageService */

?>