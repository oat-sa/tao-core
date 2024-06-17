<?php

namespace oat\tao\helpers\translation;

use oat\tao\helpers\LayoutHelper;

class SolarThemeHelper
{
    public const LANG_PREFIX = '-S';

    private LayoutHelper $layoutHelper;

    public function __construct(
        LayoutHelper $layoutHelper
    ) {
        $this->layoutHelper = $layoutHelper;
    }

    /**
     * Check if the Solar design is enabled and the prefix has not yet been added
     *
     */
    public function isContainPrefix(string $language): bool
    {
        $pattern = '/' . self::LANG_PREFIX . '$/';

        return !$this->layoutHelper->isSolarDesignEnabled() || preg_match($pattern, $language, $matches);
    }

    /**
     * Concatenate prefix for Solar design translations
     *
     */
    private function addPrefix(string $language): string
    {
        return $language . self::LANG_PREFIX;
    }

    /**
     * Check and add prefix for Solar design translations
     *
     */
    public function checkPrefix(string $language): string
    {
        if (!$this->isContainPrefix($language)) {
            $localesDir = 'views/locales';
            $dir = dirname(__FILE__) . '/../../' . $localesDir . '/' . $this->addPrefix($language);
            if (is_dir($dir)) {
                $language = $this->addPrefix($language);
            }
        }

        return $language;
    }
}
