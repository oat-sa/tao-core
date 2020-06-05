<?php

/**
 * Default config header created during install
 */

use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\index\GenerisDocumentBuilderFactory;

return new IndexService([
    'documentBuilderFactory' => new GenerisDocumentBuilderFactory()
]);
