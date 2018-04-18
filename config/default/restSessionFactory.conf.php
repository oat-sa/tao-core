<?php

return new \oat\tao\model\session\restSessionFactory\RestSessionFactory(array(
    \oat\tao\model\session\restSessionFactory\RestSessionFactory::OPTION_BUILDERS => array(
        \oat\tao\model\session\restSessionFactory\builder\HttpBasicAuthBuilder::class
    )
));