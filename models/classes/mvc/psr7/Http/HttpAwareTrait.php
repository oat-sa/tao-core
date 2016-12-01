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

namespace oat\tao\model\mvc\psr7\Http;


use Psr\Http\Message\ResponseInterface;

trait HttpAwareTrait
{

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    public function setRequest(\Psr\Http\Message\RequestInterface $request) {
        $this->request = $request;
        return $this;
    }

    /**
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest() {
        if(is_null($this->request)) {
            $this->request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
        }
        return $this->request;
    }

    /**
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse() {
        if(is_null($this->response)) {
            $this->response = new \GuzzleHttp\Psr7\Response();
        }
        return $this->response;
    }

    /**
     * @param $response \GuzzleHttp\Psr7\Response
     * @return $this
     */
    public function updateResponse(ResponseInterface $response) {
        $this->response = $response;
        return $this;
    }


}