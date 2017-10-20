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
 * Class ConfigurablePlatformTheme
 *
 * Class to easily configure a platform theme, the configuration is written to
 * /config/tao/theming.conf
 *
 * @package oat\tao\model\theme
 */

class ConfigurablePlatformTheme extends Configurable implements Theme
{

    /** Theme extension key */
    const EXTENSION_ID = 'extensionId';

    /** Theme label key */
    const LABEL = 'label';

    /** Theme id key */
    const ID = 'id';

    /** Theme stylesheet key */
    const STYLESHEET = 'stylesheet';

    /** Theme logo url key */
    const LOGO_URL = 'logoUrl';

    /** Theme logo link key */
    const LINK = 'link';

    /** Theme logo title key */
    const MESSAGE = 'message';

    /** Theme templates key */
    const TEMPLATES = 'templates';

    /** Use the default template path */
    const TEMPLATE_DEFAULT = 'useTemplateDefault';

    /**
     * Default theme path
     *
     * @var string
     */
    private $defaultThemePath = '';


    private $mandatoryOptions = [
        self::EXTENSION_ID,
        self::LABEL
    ];


    /**
     * ConfigurablePlatformTheme constructor.
     *
     * @examples
     * Only label and extensionId are configured, this will create a default configuration
     * These are the only mandatory elements
     *
     * $config = [
     *     'label' => 'Default Theme',
     *     'extensionId' => 'taoSomething'
     * ];
     * $theme = new \oat\tao\model\theme\ConfigurablePlatformTheme($config);
     *
     * This will end up as:
     * $options = [
     *     'logoUrl' => 'http://domain/taoExtension/views/img/themes/platform/my-default-theme/logo.png',
     *     'label' => 'My Default Theme',
     *     'extensionId' => 'taoExtension',
     *     'stylesheet' => 'http://domain/taoExtension/views/css/themes/platform/my-default-theme/theme.css',
     *     'id' => 'taoExtensionMyDefaultTheme'
     * ];
     *
     * If this contains anything you don't like, just add that key to your $config array to override the default.
     * The same applies if something is missing that you would like to have - for these cases generic getter is available.
     *
     * // Full blown custom configuration example
     * $config = [
     *     'label' => 'Default Theme',
     *     'extensionId' => 'taoSomething',
     *     'logoUrl' => 'http://example.com/foo.png',
     *     'link' => 'http://example.com',
     *     'message' => 'Tao Platform',
     *     'stylesheet' => 'http://example.com/tao/views/css/tao-3.css',
     *     'templates' => [
     *          'header-logo' => Template::getTemplate('blocks/header-logo.tpl', 'some-extension'),
     *
     *          // if the value of the template === ConfigurablePlatformTheme::TEMPLATE_DEFAULT
     *          // the default theme path will be used something like:
     *          // templates/themes/platform/my-default-theme/login-message.tpl
     *          'login-message' => ConfigurablePlatformTheme::TEMPLATE_DEFAULT,
     *     ],
     *     'whateverCustomStuff' => 'anything as long as the key is in camelCase'
     * ];
     *
     * @param array $options
     *
     * @throws \common_exception_MissingParameter
     */
    public function __construct(array $options=[])
    {
        // make sure label and extension id are set
        foreach($this->mandatoryOptions as $required) {
            if(empty($options[$required])) {
                throw new \common_exception_MissingParameter($required, __CLASS__);
            }
        }

        $this->setDefaultThemePath($options[static::LABEL]);

        parent::__construct(
            array_merge(
                $this->buildDefaultSetup(
                    $options[static::LABEL],
                    $options[static::EXTENSION_ID]
                ),
                $options
            )
        );
    }




    /**
     * Get a template associated from a given $id
     *
     * @param string $id
     * @param string $context
     * @return string
     */
    public function getTemplate($id, $context = Theme::CONTEXT_BACKOFFICE)
    {
        $templates = $this->getOption(static::TEMPLATES);

        if (is_null($templates) || empty($templates[$id])) {
            return Template::getTemplate('blocks/' . $id . '.tpl', 'tao');
        }

        if($templates[$id] === static::TEMPLATE_DEFAULT){
            return Template::getTemplate(
                $this->defaultThemePath . '/' . $id . '.tpl',
                $this->getOption(static::EXTENSION_ID)
            );
        }

        // otherwise it will be assumed the template is already configured
        return $templates[$id];
    }


    /**
     * This method is here to handle custom options
     *
     * @param $optionKey
     * @param $arguments
     * @return mixed
     * @throws \common_exception_NotFound
     */
    public function __call($optionKey, $arguments)
    {
        $optionKey = strtolower($optionKey[3]) . substr($optionKey, 4);
        if ($this->hasOption($optionKey)) {
            return $this->getOption($optionKey);
        }
        throw new \common_exception_NotFound('Unknown option "' . $optionKey . '"');
    }


    /**
     * Get all options
     *
     * @return array
     */
    public function getThemeData()
    {
        return $this->getOptions();
    }


    /**
     * Get the url of stylesheet associated to current theme configuration
     *
     * @param string $context
     * @return string
     */
    public function getStylesheet($context = Theme::CONTEXT_BACKOFFICE)
    {
        return $this->getOption(static::STYLESHEET);
    }


    /**
     * Get the logo url of current theme
     * Logo url is used into header
     *
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->getOption(static::LOGO_URL);
    }


    /**
     * Get the url link of current theme
     * URL is used in the header as a link for the logo
     * and in the footer for the message
     *
     * @return string
     */
    public function getLink()
    {
        if ($this->hasOption(static::LINK)) {
            return $this->getOption(static::LINK);
        }

        return '';
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
        if ($this->hasOption(static::MESSAGE)) {
            return $this->getOption(static::MESSAGE);
        }

        return '';
    }

    /**
     * Gets the label of current theme
     * Labels are useful in situations where you can choose between multiple themes
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getOption(static::LABEL);
    }

    /**
     * Gets the id of current theme
     * IDs are used to register the theme
     *
     * @return string
     */
    public function getId()
    {
        return $this->getOption(static::ID);
    }


    /**
     * Construct the common part of the default theme part
     *
     * @param string $label
     */
    protected function setDefaultThemePath($label) {
        $this->defaultThemePath = 'themes/platform/' . self::convertTextToId($label, '-');
    }


    /**
     * Converts the given text to an identifier.
     *
     * @param $text
     * @param string $separator
     *
     * @return string, either separated-by-separator or camelCase
     *
     * @TODO: this method can be reusable move to a helper class if you need to use it!
     */
    public static function convertTextToId($text, $separator='')
    {
        $id = iconv('UTF-8', 'us-ascii//TRANSLIT', $text);
        $id = preg_replace("~[^\w ]+~", '', trim(strtolower($id)));

        // separated-by-separator
        if($separator) {
            $id = preg_replace('~\s+~', $separator, $id);
        }
        // camelCase
        else {
            $id = preg_replace('~\s+~', $separator, ucwords($id));
            $id = strtolower($id[0]) . substr($id, 1);
        }
        
        return $id;
    }


    /**
     * This setup is used when configuring a theme for a custom extension. In multi tenancy though this
     * might not be relevant.
     *
     * @param $label
     * @param $extensionId
     *
     * @return array
     */
    protected function buildDefaultSetup($label, $extensionId){
        return [
            static::LABEL        => $label,
            static::EXTENSION_ID => $extensionId,
            static::ID           => $extensionId . ucfirst(self::convertTextToId($label)),
            static::LOGO_URL     => Template::img($this->defaultThemePath . '/logo.png', $extensionId),
            static::STYLESHEET   => Template::css($this->defaultThemePath . '/theme.css', $extensionId)
        ];
    }
}
