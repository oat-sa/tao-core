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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\helpers;

class JavaScript
{
    /**
     * Helps you to add the URL of the client side config file
     *
     * @param array $extraParameters additional parameters to append to the URL
     * @return string the URL
     */
    public static function getClientConfigUrl($extraParameters = array()){
        $context = \Context::getInstance();
        $clientConfigParams = [
            'extension' => $context->getExtensionName(),
            'module'    => $context->getModuleName(),
            'action'    => $context->getActionName()
        ];

        return _url('config', 'ClientConfig', 'tao', array_merge($clientConfigParams, $extraParameters));
    }
}
