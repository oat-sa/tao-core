<?php

error_reporting(E_ALL);

/**
 * TAO - tao\helpers\translation\class.RDFTranslationFile.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 03.02.2012, 16:25:41 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * A TranslationFile aiming at translating a TAO Component
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 */
require_once('tao/helpers/translation/class.TaoTranslationFile.php');

/* user defined includes */
// section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A5D-includes begin
// section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A5D-includes end

/* user defined constants */
// section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A5D-constants begin
// section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A5D-constants end

/**
 * Short description of class tao_helpers_translation_RDFTranslationFile
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */
class tao_helpers_translation_RDFTranslationFile
    extends tao_helpers_translation_TaoTranslationFile
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method addTranslationUnit
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  TranslationUnit translationUnit
     * @return mixed
     */
    public function addTranslationUnit( tao_helpers_translation_TranslationUnit $translationUnit)
    {
        // section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A5F begin
        // We override the default behaviour because for RDFTranslationFiles, TranslationUnits are
        // unique by concatening the following attributes:
        // - RDFTranslationUnit::subject
        // - RDFTranslationUnit::predicate
        // - RDFTranslationUnit::targetLanguage
        foreach ($this->getTranslationUnits() as $tu) {
        	if ($tu->hasSameTranslationUnitSubject($translationUnit) && 
        		$tu->hasSameTranslationUnitPredicate($translationUnit) &&
				$tu->hasSameTranslationUnitTargetLanguage($translationUnit)) {
					// Dismissed.
					return;
				}
        }
		
		// If we are executing this, we can add the TranslationUnit to this TranslationFile.
		$translationUnit->setSourceLanguage($this->getSourceLanguage());
		array_push($this->getTranslationUnits(), $translationUnit);
        // section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A5F end
    }

} /* end of class tao_helpers_translation_RDFTranslationFile */

?>