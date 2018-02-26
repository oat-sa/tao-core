<?php

return new \oat\tao\model\session\restSessionFactory\RestSessionFactory(array(
    \oat\tao\model\session\restSessionFactory\RestSessionFactory::OPTION_BUILDER => array(
        \oat\tao\model\session\restSessionFactory\builder\HttpBasicAuthBuilder::class
    )
));