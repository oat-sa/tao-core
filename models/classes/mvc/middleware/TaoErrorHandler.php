<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 04/05/17
 * Time: 16:47
 */

namespace oat\tao\model\mvc\middleware;


class TaoErrorHandler extends AbstractTaoMiddleware
{

    public function __invoke($request, $response, $args)
    {

        return $response
            ->withStatus(501)
            ->withHeader('Content-Type', 'text/html')
            ->write('<head><title>toto error</title></head><body>test error handler</body>');
    }

}