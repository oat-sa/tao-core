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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2022 (update and modification) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\OntologyRdf;
use oat\tao\helpers\LocaleFilesHelper;

/**
 * Internationalization helper.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 */
class tao_helpers_I18n
{
    /**
     * Short description of attribute AVAILABLE_LANGS_CACHEKEY
     *
     * @access private
     * @var string
     */
    public const AVAILABLE_LANGS_CACHEKEY = 'i18n_available_langs';

    /**
     * Short description of attribute availableLangs
     *
     * @access protected
     * @var array
     */
    protected static $availableLangs = [];

    /**
     * Load the translation strings
     *
     * @throws Exception
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public static function init(common_ext_Extension $extension, ?string $langCode): void
    {
        // if the langCode is empty do nothing
        if (empty($langCode)) {
            throw new Exception("Language is not defined");
        }
        $langCode = LocaleFilesHelper::checkPostfixDirectory($langCode);

        //init the ClearFw l10n tools
        $translations = tao_models_classes_LanguageService::singleton()->getServerBundle($langCode);
        l10n::init($translations);

        $serviceManager = \oat\oatbox\service\ServiceManager::getServiceManager();
        $extraPoService = $serviceManager->get(\oat\tao\model\i18n\ExtraPoService::SERVICE_ID);
        $extraPoCount = $extraPoService->requirePos();
    }

    /**
     * Returns the current interface language for backwards compatibility
     *
     * @access public
     * @return string
     */
    public static function getLangCode()
    {
        return LocaleFilesHelper::checkPostfixDirectory(
            common_session_SessionManager::getSession()->getInterfaceLanguage()
        );
    }

    /**
     * Returns the code of a resource
     *
     * @throws common_exception_Error|common_exception_InconsistentData
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     */
    public static function getLangResourceByCode(string $code): ?core_kernel_classes_Resource
    {
        $langs = self::getAvailableLangs();
        return isset($langs[$code]) ? new core_kernel_classes_Resource($langs[$code]['uri']) : null;
    }

    /**
     * @param unknown $code
     * @return boolean
     */
    public static function isLanguageRightToLeft($code)
    {
        $orientation = null;
        $langs = self::getAvailableLangs();
        $orientation = isset($langs[$code])
            ? $langs[$code][tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION]
            : null;
        return $orientation == tao_models_classes_LanguageService::INSTANCE_ORIENTATION_RTL;
    }

    /**
     * This method returns the languages available in TAO.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param boolean $langName If set to true, an associative array where keys are language codes and values are
     *                          language labels. If set to false (default), a simple array of language codes is
     *                          returned.
     * @return array
     * @throws common_exception_InconsistentData
     */
    private static function getAvailableLangs()
    {
        //get it into the api only once
        if (count(self::$availableLangs) == 0) {
            try {
                self::$availableLangs = common_cache_FileCache::singleton()->get(self::AVAILABLE_LANGS_CACHEKEY);
            } catch (common_cache_NotFoundException $e) {
                $langClass = new core_kernel_classes_Class(tao_models_classes_LanguageService::CLASS_URI_LANGUAGES);
                $valueProperty = new core_kernel_classes_Property(OntologyRdf::RDF_VALUE);
                foreach ($langClass->getInstances() as $lang) {
                    $values = $lang->getPropertiesValues([
                        OntologyRdf::RDF_VALUE,
                        tao_models_classes_LanguageService::PROPERTY_LANGUAGE_USAGES,
                        tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION
                    ]);
                    if (count($values[OntologyRdf::RDF_VALUE]) != 1) {
                        throw new common_exception_InconsistentData('Error with value of language ' . $lang->getUri());
                    }
                    $value = current($values[OntologyRdf::RDF_VALUE])->__toString();
                    $usages = [];
                    foreach ($values[tao_models_classes_LanguageService::PROPERTY_LANGUAGE_USAGES] as $usage) {
                        $usages[] = $usage->getUri();
                    }
                    if (count($values[tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION]) != 1) {
                        common_Logger::w('Error with orientation of language ' . $lang->getUri());
                        $orientation = tao_models_classes_LanguageService::INSTANCE_ORIENTATION_LTR;
                    } else {
                        $orientation = current(
                            $values[tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION]
                        )->getUri();
                    }
                    self::$availableLangs[$value] = [
                        'uri' => $lang->getUri(),
                        'label' => $lang->getLabel(),
                        tao_models_classes_LanguageService::PROPERTY_LANGUAGE_USAGES => $usages,
                        tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION => $orientation
                    ];
                }

                uasort(self::$availableLangs, function ($item1, $item2) {
                    return strcasecmp($item1['label'], $item2['label']);
                });

                common_cache_FileCache::singleton()->put(self::$availableLangs, self::AVAILABLE_LANGS_CACHEKEY);
            }
        }

        return self::$availableLangs;
    }

    /**
     * Get available languages from the knownledge base depending on a specific usage.
     *
     * By default, TAO considers two built-in usages:
     *
     * * GUI Language ('http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageGUI')
     * * Data Language ('http://www.tao.lu/Ontologies/TAO.rdf#LanguageUsageData')
     *
     * @author Jérôme Bogaerts <jerome@taotesting.com>
     * @param core_kernel_classes_Resource $usage Resource usage An instance of tao:LanguagesUsages from the knowledge
     *                                            base.
     * @return array An associative array of core_kernel_classes_Resource objects index by language code.
     */
    public static function getAvailableLangsByUsage(core_kernel_classes_Resource $usage)
    {
        $returnValue = [];

        foreach (self::getAvailableLangs() as $code => $langData) {
            if (in_array($usage->getUri(), $langData[tao_models_classes_LanguageService::PROPERTY_LANGUAGE_USAGES])) {
                $lang = new core_kernel_classes_Resource($langData['uri']);
                $returnValue[$code] = $lang->getLabel();
            }
        }
        return $returnValue;
    }
}
