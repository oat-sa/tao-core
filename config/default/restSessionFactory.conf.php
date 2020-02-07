<?php

return new \oat\tao\model\session\restSessionFactory\RestSessionFactory([
    \oat\tao\model\session\restSessionFactory\RestSessionFactory::OPTION_BUILDERS => [
        \oat\tao\model\session\restSessionFactory\builder\HttpBasicAuthBuilder::class
    ]
]);
