<?php

namespace oat\tao\model\i18n;

use AppendIterator;
use common_ext_ExtensionsManager as ExtensionManager;
use common_Logger;
use oat\generis\model\kernel\persistence\file\FileIterator;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdf;
use oat\oatbox\service\ConfigurableService;
use tao_install_utils_Exception;
use core_kernel_classes_Resource as KernelResource;

class LanguageService extends ConfigurableService
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'tao/language';

    public const CLASS_URI_LANGUAGES = self::URI_PREFIX . 'Languages';
    public const CLASS_URI_LANGUAGES_USAGES     = self::URI_PREFIX . 'LanguagesUsages';

    public const PROPERTY_LANGUAGE_USAGES       = self::URI_PREFIX . 'LanguageUsages';
    public const PROPERTY_LANGUAGE_ORIENTATION  = self::URI_PREFIX . 'LanguageOrientation';
    public const INSTANCE_LANGUAGE_USAGE_GUI    = self::URI_PREFIX . 'LanguageUsageGUI';
    public const INSTANCE_LANGUAGE_USAGE_DATA   = self::URI_PREFIX . 'LanguageUsageData';
    public const INSTANCE_ORIENTATION_LTR       = self::URI_PREFIX . 'OrientationLeftToRight';
    public const INSTANCE_ORIENTATION_RTL       = self::URI_PREFIX . 'OrientationRightToLeft';

    private const URI_PREFIX = 'http://www.tao.lu/Ontologies/TAO.rdf#';

    public function getAvailableLanguagesByUsage(KernelResource $usage): array
    {
        return $this->getClass(static::CLASS_URI_LANGUAGES)->searchInstances(
            [static::PROPERTY_LANGUAGE_USAGES => $usage->getUri()],
            ['like' => false]
        );
    }

    public function getLanguageByCode(string $code): ?KernelResource
    {
        $languages = $this->getClass(static::CLASS_URI_LANGUAGES)->searchInstances(
            [OntologyRdf::RDF_VALUE => $code],
            ['like' => false]
        );

        if (!count($languages)) {
            common_Logger::w('Could not find language with code ' . $code);

            return null;
        }

        return current($languages);
    }

    /**
     * @throws \core_kernel_classes_EmptyProperty
     * @throws \common_Exception
     */
    public function getCodeByLanguage(KernelResource $language): string
    {
        return (string)$language->getUniquePropertyValue($this->getProperty(OntologyRdf::RDF_VALUE));
    }

    /**
     * Convenience method that returns available language descriptions to be inserted in the knowledge base.
     *
     * @return array of ns => files
     * @throws tao_install_utils_Exception
     */
    private function getLanguageFiles(): array
    {
        $extensionManager = $this->getServiceLocator()->get(ExtensionManager::SERVICE_ID);

        $localesPath = $extensionManager->getExtensionById('tao')->getDir() . 'locales';

        if (!@is_dir($localesPath) || !@is_readable($localesPath)) {
            throw new tao_install_utils_Exception("Cannot read 'locales' directory in 'tao' extension.");
        }

        $files = [];

        foreach (scandir($localesPath) as $localeDir) {
            $path = $localesPath . '/' . $localeDir;
            if ($localeDir[0] !== '.' && @is_dir($path)) {
                // Look if the lang.rdf can be read.
                $languageModelFile = $path . '/lang.rdf';
                if (@file_exists($languageModelFile) && @is_readable($languageModelFile)) {
                    $files[] = $languageModelFile;
                }
            }
        }

        return $files;
    }

    /**
     * Returns the definition of the languages as an RDF iterator
     * @throws tao_install_utils_Exception
     */
    public function getLanguagesDefinition(): AppendIterator
    {
        $model = new AppendIterator();

        foreach ($this->getLanguageFiles() as $rdfPath) {
            $iterator = new FileIterator($rdfPath);
            $model->append($iterator->getIterator());
        }

        return $model;
    }
}
