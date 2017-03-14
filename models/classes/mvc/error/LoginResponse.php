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
 *  Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\mvc\error;

/**
 * redirect to tao home login
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class LoginResponse extends ResponseAbstract {
    
    protected $contentType = 'text/plain';


    public function setHttpCode($code) {
        $this->httpCode = 302;
        return $this;
    }
    
    public function send() {
        $params = [];
        if ($this->exception instanceof \tao_models_classes_AccessDeniedException) {
            $params = [
                'redirect' => $this->exception->getDeniedRequest()->getRequestURI(),
                'msg' => $this->exception->getUserMessage()
            ];
        }
        /* @var $urlRouteService \oat\tao\model\mvc\DefaultUrlService */
        $urlRouteService = $this->getServiceLocator()->get('tao/urlroute');
        header(\HTTPToolkit::locationHeader($urlRouteService->getLoginUrl($params)));
        return;
    }
    
}
