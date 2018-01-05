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

use oat\generis\model\OntologyRdf;
use oat\tao\helpers\translation\TranslationBundle;
use oat\generis\model\data\ModelManager;
use oat\tao\helpers\translation\rdf\RdfPack;

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
    const CLASS_URI_LANGUAGES = 'http://www.tao.lu/Ontologies/TAO.rdf#Languages';
    const CLASS_URI_LANGUAGES_USAGES = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguagesUsages';
    const PROPERTY_LANGUAGE_USAGES = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsages';
    const PROPERTY_LANGUAGE_ORIENTATION = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageOrientation';
    const INSTANCE_LANGUAGE_USAGE_GUI = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageGUI';
    const INSTANCE_LANGUAGE_USAGE_DATA = 'http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageData';
    const INSTANCE_ORIENTATION_LTR = 'http://www.tao.lu/Ontologies/TAO.rdf#OrientationLeftToRight';
    const INSTANCE_ORIENTATION_RTL = 'http://www.tao.lu/Ontologies/TAO.rdf#OrientationRightToLeft';
    // --- OPERATIONS ---

    /**
     * Short description of method createLanguage
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string $code
     * @return core_kernel_classes_Resource
     * @throws common_exception_Error   Not implemented in this class yet.
     */
    public function createLanguage($code)
    {
        throw new common_exception_Error(__METHOD__.' not yet implemented in '.__CLASS__);
    }

    /**
     * Short description of method getLanguageByCode
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string $code
     * @return core_kernel_classes_Resource
     */
    public function getLanguageByCode($code)
    {
        $returnValue = null;


        $langClass = new core_kernel_classes_Class(static::CLASS_URI_LANGUAGES);
	    $langs = $langClass->searchInstances(array(
            OntologyRdf::RDF_VALUE => $code
	    ), array(
	    	'like' => false
	    ));
	    if (count($langs) == 1) {
	    	$returnValue = current($langs);
	    } else {
	    	common_Logger::w('Could not find language with code '.$code);
	    }


        return $returnValue;
    }

    /**
     * Short description of method getCode
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  core_kernel_classes_Resource $language
     * @return string
     */
    public function getCode( core_kernel_classes_Resource $language)
    {
        $returnValue = (string) '';
        $valueProperty = new core_kernel_classes_Property(OntologyRdf::RDF_VALUE);
        $returnValue = $language->getUniquePropertyValue($valueProperty);
        return (string) $returnValue;
    }

    /**
     * Short description of method getAvailableLanguagesByUsage
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  core_kernel_classes_Resource $usage
     * @return array
     */
    public function getAvailableLanguagesByUsage( core_kernel_classes_Resource $usage)
    {
        $returnValue = array();
    	$langClass = new core_kernel_classes_Class(static::CLASS_URI_LANGUAGES);
	    $returnValue = $langClass->searchInstances(array(
            static::PROPERTY_LANGUAGE_USAGES => $usage->getUri()
	    ), array(
	    	'like' => false
	    ));
        return (array) $returnValue;
    }

    /**
     * Checks the language availability in the given context(usage).
     *
     * @param string                       $code    The language code to check. (for example: en-US)
     * @param core_kernel_classes_Resource $usage   The context of the availability.
     *
     * @return bool
     */
    public function isLanguageAvailable($code, core_kernel_classes_Resource $usage)
    {
        $langClass = new core_kernel_classes_Class(static::CLASS_URI_LANGUAGES);
        $result = $langClass->searchInstances(
            array(
                OntologyRdf::RDF_VALUE => $code,
                static::PROPERTY_LANGUAGE_USAGES => $usage->getUri(),
            ),
            array('like' => false)
        );

        return !empty($result);
    }

    public function addTranslationsForLanguage(core_kernel_classes_Resource $language)
    {
        $langCode = $this->getCode($language);
        $rdf = ModelManager::getModel()->getRdfInterface();

        $extensions = common_ext_ExtensionsManager::singleton()->getInstalledExtensions();
        foreach ($extensions as $extension) {
            $pack = new RdfPack($langCode, $extension);
            foreach ($pack as $triple) {
                $rdf->add($triple);
            }
        }
    }


    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     *
     * @param bool $checkPreviousBundle
     *
     * @return array
     */
    public function generateClientBundles($checkPreviousBundle = false)
    {
        $returnValue = array();

        $extensions = array_map(
            function ($extension) {
                return $extension->getId();
            },
            common_ext_ExtensionsManager::singleton()->getInstalledExtensions()
        );
        // lookup for languages into tao
        $languages = tao_helpers_translation_Utils::getAvailableLanguages();
        $path = ROOT_PATH . 'tao/views/locales/';
        $generated = 0;
        $generate = true;
        foreach ($languages as $langCode) {

            try {
                $bundle = new TranslationBundle($langCode, $extensions, ROOT_PATH, TAO_VERSION);

                if ($checkPreviousBundle) {
                    $currentBundle = $path . $langCode . '.json';
                    if (file_exists($currentBundle)) {
                        $bundleData = json_decode(file_get_contents($currentBundle), true);
                        if ($bundleData['serial'] === $bundle->getSerial()) {
                            $generate = false;
                        }
                    }
                    if ($generate) {
                        $file = $bundle->generateTo($path, false);
                    }
                } else {
                    $file = $bundle->generateTo($path);
                }
                if ($file) {
                    $generated ++;
                    $returnValue[] = $file;
                } else {
                    if ($generate) {
                        common_Logger::e('Failure generating message.js for lang ' . $langCode);
                    } else {
                        common_Logger::d('Actual File is more recent, skip ' . $langCode);
                    }
                }
            } catch (common_exception_Error $e) {

                common_Logger::e('Failure: ' . $e->getMessage());
            }
        }
        common_Logger::i($generated . ' translation bundles have been (re)generated');

        return $returnValue;
    }

    /**
     * Short description of method getDefaultLanguageByUsage
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  core_kernel_classes_Resource $usage
     * @return core_kernel_classes_Resource
     * @throws common_exception_Error   Not implemented in this class yet.
     */
    public function getDefaultLanguageByUsage( core_kernel_classes_Resource $usage)
    {
        throw new common_exception_Error(__METHOD__.' not yet implemented in '.__CLASS__);
    }

}

?>
