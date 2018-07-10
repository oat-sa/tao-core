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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\model\clientConfig\sources;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\clientConfig\ClientConfig;
use oat\tao\model\ThemeRegistry;
use oat\taoLti\models\classes\TaoLtiSession;

/**
 * 
 * @author Joel Bout
 */
class ThemeConfig extends ConfigurableService implements ClientConfig {

    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\clientConfig\ClientConfig::getConfig()
     */
    public function getConfig() {
        $config = ThemeRegistry::getRegistry()->getAvailableThemes();

        $sessionSingleton = \PHPSession::singleton();

        $deliveryExecutionUri = $this->getDeliveryExecutionFromReferer();

        if (null !== $deliveryExecutionUri) {
            $deliveryUriAttribute = $this->getDeliveryUriAttribute($deliveryExecutionUri);

            if (false !== $sessionSingleton->hasAttribute($deliveryUriAttribute)) {
                $deliveryUri = $sessionSingleton->getAttribute($deliveryUriAttribute);

                $delivery = new \core_kernel_classes_Resource($deliveryUri);

                $themeName = $delivery->getOnePropertyValue(new \core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAODelivery.rdf#ThemeName'));

                if (null !== $themeName) {
                    $config["activeNamespace"] = (string)$themeName;
                }
            }
        }

        return $config;
    }

    /**
     * Gets deliveryExecution URI from referer of current request
     *
     * @return null
     */
    private function getDeliveryExecutionFromReferer()
    {
        $currentRequest = \Context::getInstance()->getRequest();
        $referer = $currentRequest->getHeader('referer');

        if (!empty($referer)) {
            $parsedString = parse_url($referer);

            if (array_key_exists('query', $parsedString) && !empty($parsedString['query'])) {
                $queryString = $parsedString['query'];
                parse_str($queryString, $parsedQuery);

                if (array_key_exists('deliveryExecution', $parsedQuery) && !empty($parsedQuery['deliveryExecution'])) {
                    return $parsedQuery['deliveryExecution'];
                }
            }
        }

        return null;
    }

    /**
     * Gets session key for delivery URI
     *
     * @param $deliveryExecutionId
     * @return string
     */
    private function getDeliveryUriAttribute($deliveryExecutionId)
    {
        return 'deliveryIdForDeliveryExecution:' . $deliveryExecutionId;
    }
}
