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
namespace oat\tao\model\middleware;

use \Closure;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MiddlewareManager
{
    protected $middlewares;

    public function add(Callable $middleware)
    {
        if (!isset($this->middlewares[$middleware])) {
            $this->middlewares[] = $middleware;
        }
        return $this;
    }

    public function run(\Request $request)
    {
        $response = $this->before($request);
        $response = $this->after($request, $response);
        return $response;
    }

    public function before(RequestInterface $request)
    {
        $response = new Response();

        foreach ($this->middlewares as $key => $middleware) {
//            $next = isset($this->middlewares[$key+1]) ? $this->middlewares[$key+1] : null;

            $body = $response->getBody();
            if ($middleware instanceof Middleware) {
                $body .= call_user_func_array(array($middleware, 'handle'), array($request));
            } else {
                $body .= call_user_func_array(array($middleware), array($request, $next));
            }
            $response->withBody($body);
        }

        return $response;
    }

    public function after(RequestInterface $request, ResponseInterface $response)
    {
        $body = $response->getBody();
        foreach ($this->middlewares as $key => $middleware) {
            if ($middleware instanceof Middleware) {
                $body .= call_user_func_array(array($middleware, 'terminate'), array($request, $response));
            } else {
                $body .= call_user_func_array(array($middleware), array($request, $response));
            }
            $response->withBody($body);
        }
        return $response;
    }
}