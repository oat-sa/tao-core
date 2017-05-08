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

namespace oat\tao\model\mvc\psr7;

use oat\tao\model\mvc\middleware\TaoControllerExecution;
use oat\tao\model\mvc\psr7\clearfw\Request;
use oat\tao\model\mvc\psr7\clearfw\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Uri;

/**
 * psr7 request controller
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class Controller extends \tao_actions_CommonModule {
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var Response
     */
    protected $response;

    /**
     * @return Request
     */
    public function getRequest() {
        if(is_null($this->request)) {
            $this->request = new \oat\tao\model\mvc\psr7\clearfw\Request();
            $this->request->setPsrRequest(\GuzzleHttp\Psr7\ServerRequest::fromGlobals());
        }
        return $this->request;
    }
    
    /**
     * @return Response
     */
    public function getResponse() {
        if(is_null($this->response)) {
            $this->response = new \oat\tao\model\mvc\psr7\clearfw\Response();
            $this->response->setPsrResponse(new \GuzzleHttp\Psr7\Response());
        }
        return $this->response;
    }

    public function setPsr7(ServerRequestInterface $request, ResponseInterface $response) {
        $this->request = new \oat\tao\model\mvc\psr7\clearfw\Request();
        $this->request->setPsrRequest($request);

        $this->response = new \oat\tao\model\mvc\psr7\clearfw\Response();
        $this->response->setPsrResponse($response);

        return $this;
    }

    /**
     * 
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getPsrRequest() {
        return $this->getRequest()->getPsrRequest();
    }
    
    /**
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getPsrResponse() {
        return $this->getResponse()->getPsrResponse();
    }

     /**
     * @param $response \GuzzleHttp\Psr7\Response
     * @return $this
     */
    public function updateResponse(ResponseInterface $response) {
        $this->getResponse()->setPsrResponse($response);
        return $this;
    }
    
    /**
     * @return ResponseInterface
     */
    public function sendResponse($response = null) {
        if($this->hasView()) {
            $view = $this->getRenderer()->render();
        } else {
            $view = '';
        }
        if(!is_a($response, \GuzzleHttp\Psr7\Response::class )) {
            /* @var $response \GuzzleHttp\Psr7\Response */
            $response = $this->getResponse()->getPsrResponse();
        }
        $body     = \GuzzleHttp\Psr7\stream_for($view);
        $response = $response->withBody($body);
        
        return $response;
    }

    /**
     * Forward using the TAO FlowController implementation
     * @see {@link oat\model\routing\FlowController}
     */
    public function forward($action, $controller = null, $extension = null, $params = array())
    {
        $uriString = \tao_helpers_Uri::url($action, $controller, $extension, $params);
        return $this->forwardUrl($uriString);
    }

    /**
     * Forward using the TAO FlowController implementation
     */
    public function forwardUrl($url)
    {
        $Uri = Uri::createFromString($url);
        $newRequest = $this->getPsrRequest()->withUri($Uri);
        $container = $this->getServiceManager()->get('tao/slimContainer')->configure()->getContainer();
        $middleWare = new TaoControllerExecution($container);

        $this->updateResponse($middleWare($newRequest , $this->getPsrResponse() , []));

        return $this->getPsrResponse();

    }

    /**
     * Redirect using the TAO FlowController implementation
     * @param $url
     * @param int $statusCode
     * @return ResponseInterface
     */
    public function redirect($url, $statusCode = 302)
    {
        $response = $this->getPsrResponse()

            ->withStatus($statusCode)->withAddedHeader('Location' , $url);

        $this->updateResponse($response);
        return $response;
    }
    
}
