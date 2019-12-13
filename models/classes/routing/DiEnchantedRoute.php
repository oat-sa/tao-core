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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\routing;


use oat\tao\controller\entry\DummyStatic;
use Psr\Http\Message\ServerRequestInterface;
use tao_helpers_Request;

/**
 * A simple router, that maps a relative Url to
 * namespaced Controller class
 */
class DiEnchantedRoute extends AbstractRoute
{
    public function resolve(ServerRequestInterface $request)
    {
        $relativeUrl = tao_helpers_Request::getRelativeUrl($request->getRequestTarget());
        if (array_key_exists($relativeUrl, $this->getDiInchantedActions())) {
            return $this->getDiInchantedActions()[$relativeUrl];
        }
        return null;
    }

    /**
     * Get controller namespace prefix
     * @return string
     */
    public static function getControllerPrefix()
    {
        return '';
    }


    private function getDiInchantedActions()
    {
        return [
            'tao/Main/login' => \oat\tao\controller\entry\Login::class . '@login',
            'tao/DummyStatic/test' => DummyStatic::class . '@test',
        ];
    }
}
