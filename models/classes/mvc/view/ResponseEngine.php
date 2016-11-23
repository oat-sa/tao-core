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
 *
 */

namespace oat\tao\model\mvc\view;

/**
 * Description of ResponseEngine
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class ResponseEngine implements ResponseEngineActionInterface {
    
    /**
     * @inherit
     */
    public function isUsable(\oat\tao\model\mvc\psr7\Controller $controller) {
        if($controller->getResponse() instanceof \oat\tao\model\mvc\psr7\clearfw\Response) {
            return true;
        }
        return false;
    }
    
    /**
     * @inherit
     */
    public function send(\oat\tao\model\mvc\psr7\Controller $controller) {
        
        $response = $controller->getResponse();
        
        if($controller->hasView()) {
            $renderer   = $controller->getRenderer();
            $bodyString = $renderer->render();
        }
        
    }

}
