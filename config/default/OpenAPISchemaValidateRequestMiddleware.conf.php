<?php
/**
 * Default config header created during install
 */

use oat\tao\model\Middleware\OpenAPISchemaValidateRequestMiddleware;

return new oat\tao\model\Middleware\OpenAPISchemaValidateRequestMiddleware([
    OpenAPISchemaValidateRequestMiddleware::OPTION_SCHEMA_MAP => [
    ]
]);
