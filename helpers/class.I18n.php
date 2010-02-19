<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/class.I18n.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 03.02.2010, 13:59:15 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7B-includes begin
// section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7B-includes end

/* user defined constants */
// section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7B-constants begin
// section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7B-constants end

/**
 * Short description of class tao_helpers_I18n
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_I18n
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute langCode
     *
     * @access private
     * @var Integer
     */
    private static $langCode = null;

    // --- OPERATIONS ---

    /**
     * Short description of method init
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string langCode
     * @return mixed
     */
    public static function init($langCode)
    {
        // section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7C begin
		
		self::$langCode = $langCode;
		l10n::init();
		l10n::set(BASE_PATH.'/locales/'.self::$langCode.'/messages');
		
		tao_helpers_Scriptloader::addJsFiles(array(
			BASE_URL . '/locales/'.self::$langCode.'/messages_po.js',
			TAOBASE_WWW . 'js/i18n.js',
		));
		
		$_SESSION['lang'] = self::$langCode;
		
        // section 127-0-1-1--7d879eb4:12693e522d7:-8000:0000000000001E7C end
    }

} /* end of class tao_helpers_I18n */

?>