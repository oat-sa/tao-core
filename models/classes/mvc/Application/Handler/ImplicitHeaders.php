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

namespace oat\tao\model\mvc\Application\Handler;



use Psr\Http\Message\ResponseInterface;

class ImplicitHeaders
{

    /**
     * store catched implicit headers
     * @var array
     */
    protected $implicitHeaders = [];


    public function catchHeaders() {

        $headers = headers_list();
        header_remove();

        foreach ($headers as $header) {

            list($name , $value) = explode(':' , $header);

            $this->addImplicitHeader($name , $value);

        }
        return $this;

    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function setUpResponse(ResponseInterface $response) {
        foreach ($this->implicitHeaders as $name => $value) {
            $response = $response->withAddedHeader($name , $value);
        }
        return $response;
    }

    protected function addImplicitHeader($name , $value) {
        $this->implicitHeaders[$name] = trim($value);
        return $this;
    }

    public function getImplicitHeaders() {
        return $this->implicitHeaders;
    }


}