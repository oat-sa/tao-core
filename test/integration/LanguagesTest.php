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
 */

use oat\generis\model\OntologyRdf;
use oat\tao\model\TaoOntology;
use oat\tao\test\TaoPhpUnitTestRunner;

/**
 * Aims at testing languages/locales in TAO.
 *
 * @author Jerome Bogaerts, <taosupport@tudor.lu>
 * @package tao
 */
class LanguagesTestCase extends TaoPhpUnitTestRunner
{
    /**
     * Test the existence of language definition resources in the knowledge base
     * regarding what we found in the tao/locales directory.
     *
     * @author Jerome Bogaerts, <taosupport@tudor.lu>
     */
    public function testLanguagesExistence()
    {
        // Check for lang.rdf in /tao locales and query the KB to see if it exists or not.
        $languageClass = new core_kernel_classes_Class(tao_models_classes_LanguageService::CLASS_URI_LANGUAGES);
        $taoLocalesDir = ROOT_PATH . '/tao/locales';
        $expectedUriPrefix = 'http://www.tao.lu/Ontologies/TAO.rdf#Lang';

        if (false !== ($locales = scandir($taoLocalesDir))) {
            foreach ($locales as $l) {
                $localePath = $taoLocalesDir . '/' . $l;
                if ($l[0] !== '.' && is_dir($localePath) && is_readable($localePath)) {
                    $langPath = $localePath . '/lang.rdf';

                    if (file_exists($langPath)) {
                        $lgResource = new core_kernel_classes_Resource($expectedUriPrefix . $l);
                        $this->assertTrue(
                            $lgResource->exists(),
                            '$lgResource Resource does not exist (' . $expectedUriPrefix . $l . ').'
                        );

                        // Check for this language in Ontology.
                        $kbLangs = $lgResource->getPropertyValues(
                            new core_kernel_classes_Property(OntologyRdf::RDF_VALUE)
                        );
                        if (is_array($kbLangs)) {
                            $this->assertCount(
                                1,
                                $kbLangs,
                                "Number of languages retrieved for language '${l}' is '" . count($kbLangs) . "'."
                            );

                            // Check if the language has the correct URI.
                            if ($kbLangs[0] instanceof core_kernel_classes_Resource) {
                                $this->assertTrue(
                                    $kbLangs[0]->getUri() == $expectedUriPrefix . $l,
                                    "Malformed URI scheme for language resource '${l}'."
                                );
                            }
                        } else {
                            $this->fail(
                                'the $kbLangs variable should be an array. "' . gettype($kbLangs) . '" found instead.'
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Test the language usages property. The usages property defines
     * in which context a language can take place e.g. GUI, data, ...
     *
     * @author Jerome Bogaerts, <taosupport@tudor.lu>
     */
    public function testLanguageUsages()
    {
        // The default locale should always exists and should
        // be available in any known language usage.
        $lgUsageProperty = new core_kernel_classes_Property(
            tao_models_classes_LanguageService::PROPERTY_LANGUAGE_USAGES
        );

        $this->assertInstanceOf(core_kernel_classes_Property::class, $lgUsageProperty);
        $this->assertEquals(
            $lgUsageProperty->getUri(),
            tao_models_classes_LanguageService::PROPERTY_LANGUAGE_USAGES
        );

        $usagePropertyRange = $lgUsageProperty->getRange();
        $this->assertInstanceOf(core_kernel_classes_Class::class, $usagePropertyRange);
        $this->assertEquals(
            $usagePropertyRange->getUri(),
            tao_models_classes_LanguageService::CLASS_URI_LANGUAGES_USAGES
        );

        $instancePrefix = 'http://www.tao.lu/Ontologies/TAO.rdf#Lang';
        $targetLanguageCode = DEFAULT_LANG;
        $valueProperty = new core_kernel_classes_Property(OntologyRdf::RDF_VALUE);

        $defaultLanguage = new core_kernel_classes_Resource($instancePrefix . $targetLanguageCode);
        $this->assertIsA($defaultLanguage, 'core_kernel_classes_Resource');
        $this->assertTrue(in_array(DEFAULT_LANG, $defaultLanguage->getPropertyValues($valueProperty)));

        $usages = $defaultLanguage->getPropertyValues($lgUsageProperty);
        $this->assertTrue(count($usages) >= 2);
        $this->assertTrue(in_array(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_GUI, $usages));
        $this->assertTrue(in_array(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA, $usages));
    }
}
