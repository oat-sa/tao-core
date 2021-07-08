<?php

namespace oat\tao\model\i18n;

use AppendIterator;
use common_ext_ExtensionsManager as ExtensionManager;
use oat\generis\model\kernel\persistence\file\FileIterator;
use oat\oatbox\service\ConfigurableService;
use tao_install_utils_Exception;

class LanguageService extends ConfigurableService
{
    public const SERVICE_ID = 'tao/language';

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
