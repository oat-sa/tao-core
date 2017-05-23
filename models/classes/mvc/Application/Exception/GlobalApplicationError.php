<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 23/05/17
 * Time: 11:58
 */

namespace oat\tao\model\mvc\Application\Exception;


class GlobalApplicationError extends \common_exception_Error implements \common_exception_UserReadableException
{

    public function getUserMessage()
    {
        return __('an error occurred');
    }

}