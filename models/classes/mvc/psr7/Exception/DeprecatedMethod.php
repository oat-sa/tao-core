<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 08/05/17
 * Time: 10:23
 */

namespace oat\tao\model\mvc\psr7\Exception;


use Exception;

class DeprecatedMethod extends \common_exception_Error
{

    public function __construct($method)
    {
        parent::__construct($method . 'is deprecated');
    }

}