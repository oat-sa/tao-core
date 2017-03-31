<?php
/*
 * This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\mvc\middleware;

use oat\tao\model\mvc\psr7\Resolver;

/**
 * Middleware to resolve route to controller
 * Class TaoResolver
 * @package oat\tao\model\mvc\middleware
 */
class TaoResolver extends AbstractTaoMiddleware
{

    public function __invoke($request, $response, $args)
    {
        try {
            /**
             * @var $resolver Resolver
             */
            $resolver = $this->container->get('resolver');
            $resolver->setRequest($request);
            $extId = $resolver->getExtensionId();

            // load the responsible extension
            $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById($extId);

            $this->container->get('context')->setExtensionName($resolver->getExtensionId());
            // load translations
            $uiLang = \common_session_SessionManager::getSession()->getInterfaceLanguage();
            \tao_helpers_I18n::init($ext, $uiLang);
            return $response;
        } catch(\Exception $e) {
            var_dump($e->getTraceAsString());die();
        }
    }

}