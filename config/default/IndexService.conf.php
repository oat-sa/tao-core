<?php

/**
 * Default config header created during install
 */

use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilder;

return new IndexService([
    'documentBuilder' => new IndexDocumentBuilder()
]);
