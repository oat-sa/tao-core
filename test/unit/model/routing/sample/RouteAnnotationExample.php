<?php
/**
 * Created by PhpStorm.
 * User: zagovorychev
 * Date: 2018-12-26
 * Time: 13:43
 */

namespace oat\tao\test\unit\model\routing\sample;


use oat\tao\model\routing\RouteAnnotation;

class RouteAnnotationExample
{
    /**
     * @RouteAnnotation("hidden");
     */
    public function notFoundAnnotation() { }

    /**
     * Nothing needs to be done
     */
    public function withoutAnnotation() { }
}
