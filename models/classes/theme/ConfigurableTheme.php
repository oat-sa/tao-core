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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\theme;

use oat\oatbox\Configurable;
use oat\tao\helpers\Template;

/**
 * Class UrlSourceTheme
 *
 * Class to easily configure a theme
 * To use it, declare into tao/theming.conf themes:
 *
 * return new oat\tao\model\theme\ThemeService(array(
 * 'available' => array(
 *    [...],
 *    'test' => new \oat\tao\model\theme\ConfigurableTheme(),
 *    'testConfigured' => new \oat\tao\model\theme\ConfigurableTheme(array(
 *       'data' => array(
 *          'logo-url' => 'http://lorempixel.com/400/200,
 *          'link' => 'http://taotesting.com',
 *          'message' => 'Tao Platform',
 *          'label' => 'Default Theme',
 *          'id' => 'defaultTheme'
 *        ),
 *        'stylesheet' => 'http://tao.dev/tao/views/css/tao-3.css'
 *     )
 *  )
 *  [...]
 *
 * @package oat\tao\model\theme
 * @deprecated use oat\tao\model\theme\ConfigurablePlatformTheme instead
 */
class ConfigurableTheme extends Configurable implements Theme
{
    /** Theme id offset in the options. */
    const THEME_ID    = 'id';

    /** Theme label offset in the options. */
    const THEME_LABEL = 'label';

    /** Theme data offset in the options. */
    const THEME_DATA  = 'data';

    /** Theme css offset in the options. */
    const THEME_CSS   = 'stylesheet';

    /** Theme data logo url offset in the options under the data offset. */
    const THEME_DATA_LOGO_URL = 'logo-url';
    /** Theme data logo link offset in the options under the data offset. */
    const THEME_DATA_LINK     = 'link';
    /** Theme data logo title offset in the options under the data offset. */
    const THEME_DATA_MESSAGE  = 'message';

    /**
     * Defined custom texts
     * @var array
     */
    private $allTexts;

    /**
     * Define all custom text
     * return [
     *  'myCustomTextId' => __('My custom text translation');
     * ];
     * @return array
     */
    protected function initializeTexts()
    {
        return [];
    }

    /**
     * Allow to set a custom translatable string for a given key
     * @param String $key
     * @return string
     */
    public function getText($key)
    {
        if (empty($this->allTexts)) {
            $this->allTexts = $this->initializeTexts();
        }
        return (array_key_exists($key, $this->allTexts))
            ? $this->allTexts[$key]
            : '';
    }

    /**
     * Retrieve all custom strings for the given keys
     * @param String[] $allKeys
     * @return array
     */
    public function getTextFromArray($allKeys)
    {
        $allValues = [];
        if (is_array($allKeys) && ! empty($allKeys)) {
            foreach ($allKeys as $key) {
                $allValues[$key] = $this->getText($key);
            }
        }
        return $allValues;
    }

    /**
     * Retrieve all existing strings
     * @return array
     */
    public function getAllTexts()
    {
        if (empty($this->allTexts)) {
            $this->allTexts = $this->initializeTexts();
        }
        return $this->allTexts;
    }

    /**
     * Get a template associated to given $id
     *
     * @param string $id
     * @param string $context
     * @return null|string
     */
    public function getTemplate($id, $context = Theme::CONTEXT_BACKOFFICE)
    {
        switch ($id) {
            case 'head':
                $template = Template::getTemplate('blocks/head.tpl', 'tao');
                break;
            case 'header-logo':
                $template = Template::getTemplate('blocks/header-logo.tpl', 'tao');
                break;
            case 'footer':
                $template = Template::getTemplate('blocks/footer.tpl', 'tao');
                break;
            case 'login-message':
                $template = Template::getTemplate('blocks/login-message.tpl', 'tao');
                break;
            default:
                \common_Logger::w('Unknown template ' . $id);
                $template = null;
        }
        return $template;
    }

    /**
     * Get options under data key
     * Options to configure header & footer template
     *
     * @return array
     */
    public function getThemeData()
    {
        if ($this->hasOption(static::THEME_DATA) && is_array($this->getOption(static::THEME_DATA))) {
            return $this->getOption(static::THEME_DATA);
        }
        
        return [];
    }

    /**
     * Get the url of stylesheet associated to current theme configuration
     *
     * @param string $context
     * @return string
     */
    public function getStylesheet($context = Theme::CONTEXT_BACKOFFICE)
    {
        if ($this->hasOption(static::THEME_CSS)) {
            return $this->getOption(static::THEME_CSS);
        }
        
        return Template::css('tao-3.css', 'tao');
    }

    /**
     * Get the logo url of current theme
     * Logo url is used into header
     *
     * @return string
     */
    public function getLogoUrl()
    {
        $data = $this->getThemeData();
        if (isset($data[static::THEME_DATA_LOGO_URL])) {
            return $data[static::THEME_DATA_LOGO_URL];
        }
        
        return Template::img('tao-logo.png', 'tao');
    }

    /**
     * Get the url link of current theme
     * Url is used into header, to provide link to logo
     * Url is used into footer, to provide link to footer message
     *
     * @return string
     */
    public function getLink()
    {
        $data = $this->getThemeData();
        if (isset($data[static::THEME_DATA_LINK])) {
            return $data[static::THEME_DATA_LINK];
        }
        
        return 'http://taotesting.com';
    }

    /**
     * Get the message of current theme
     * Message is used in the header as title of the logo
     * Message is used in the footer as footer message
     *
     * @return string
     */
    public function getMessage()
    {
        $data = $this->getThemeData();
        if (isset($data[static::THEME_DATA_MESSAGE])) {
            return $data[static::THEME_DATA_MESSAGE];
        }
        
        return '';
    }

    /**
     * Get the label of current theme
     * Labels are useful in situations where you can choose between multiple themes
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->hasOption(static::THEME_LABEL)) {
            return $this->getOption(static::THEME_LABEL);
        }

        return '';
    }

    /**
     * Get the id of current theme
     * IDs are used to register the theme
     *
     * @return string
     */
    public function getId()
    {
        if ($this->hasOption(static::THEME_ID)) {
            return $this->getOption(static::THEME_ID);
        }

        return '';
    }
}
