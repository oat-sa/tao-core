<?php

declare(strict_types = 1);

namespace oat\tao\model\i18n;

use common_cache_Cache as CacheManager;
use common_cache_NotFoundException;
use common_exception_Error;
use common_ext_ExtensionsManager as ExtensionsManager;
use common_Logger;
use helpers_ExtensionHelper as ExtensionHelper;
use l10n;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\helpers\translation\TranslationBundle;
use tao_helpers_translation_Utils;

class TranslationService extends ConfigurableService
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'tao/translation';

    /**
     * Regenerates client and server translations
     * @return string[] list of client files regenerated
     */
    public function generateAll(bool $checkPreviousBundle = false): array
    {
        $this->generateServerBundles();

        return $this->generateClientBundles($checkPreviousBundle);
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     *
     * @param bool $checkPreviousBundle
     *
     * @return array
     */
    protected function generateClientBundles(bool $checkPreviousBundle = false): array
    {
        $returnValue = [];

        $extensions = array_map(
            static function ($extension) {
                return $extension->getId();
            },
            $this->getExtensionManager()->getInstalledExtensions()
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
                        $file = $bundle->generateTo($path);
                    }
                } else {
                    $file = $bundle->generateTo($path);
                }
                if ($file) {
                    $generated++;
                    $returnValue[] = $file;
                } elseif ($generate) {
                    common_Logger::e('Failure generating messages_po.js for lang ' . $langCode);
                } else {
                    common_Logger::d('Actual File is more recent, skip ' . $langCode);
                }
            } catch (common_exception_Error $e) {
                common_Logger::e('Failure: ' . $e->getMessage());
            }
        }
        common_Logger::i($generated . ' translation bundles have been (re)generated');

        return $returnValue;
    }

    /**
     * Generate server translation file, forcing a cache overwrite
     *
     */
    protected function generateServerBundles(): void
    {
        /** @var LanguageService $languageService */
        $languageService = $this->getServiceLocator()->get(LanguageService::SERVICE_ID);

        $usage = $this->getResource(LanguageService::INSTANCE_LANGUAGE_USAGE_GUI);

        foreach ($languageService->getAvailableLanguagesByUsage($usage) as $language) {
            $this->generateLanguageBundle($languageService->getCodeByLanguage($language));
        }
    }

    /**
     * Returns the translation strings for a given language
     * Conflicting translations get resolved by order of dependencies
     *
     * @param string $language
     *
     * @return array translation strings
     * @throws \common_exception_Error
     * @throws \common_ext_ExtensionException
     */
    public function getServerBundle(string $language): array
    {
        try {
            $translations = $this->getCacheManager()->get(self::TRANSLATION_PREFIX . $language);
        } catch (common_cache_NotFoundException $ex) {
            $translations = $this->generateLanguageBundle($language);
        }

        return $translations;
    }

    /**
     * Rebuild the translation cache from the PO files situated in each installed extension
     *
     * @param string $language
     *
     * @return array translation
     * @throws \common_exception_Error
     * @throws \common_ext_ExtensionException
     */
    protected function generateLanguageBundle(string $language): array
    {
        // todo: reduce usage of helpers
        $extensions = ExtensionHelper::sortByDependencies(
            $this->getExtensionManager()->getInstalledExtensions()
        );

        $translations = [];
        foreach ($extensions as $extension) {
            $file = implode(DIRECTORY_SEPARATOR, [
                $extension->getDir() . 'locales',
                $language,
                'messages.po'
            ]);

            $new = l10n::getPoFile($file);

            if (is_array($new)) {
                $translations = array_merge($translations, $new);
            }
        }

        $this->getCacheManager()->put($translations, self::TRANSLATION_PREFIX . $language);
//        'tao_models_classes_LanguageService::all'
//        public const TRANSLATION_PREFIX = __CLASS__ . ':all';

        return $translations;
    }

    protected function getExtensionManager(): ExtensionsManager
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);
    }

    protected function getCacheManager(): CacheManager
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(CacheManager::SERVICE_ID);
    }
}
