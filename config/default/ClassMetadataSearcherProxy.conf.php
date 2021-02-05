<?php

use oat\tao\model\Lists\Business\Service\ClassMetadataSearcherProxy;
use oat\tao\model\Lists\Business\Service\ClassMetadataService;

return new ClassMetadataSearcherProxy(
    [
        ClassMetadataSearcherProxy::OPTION_SEARCHERS => [
            ClassMetadataService::SERVICE_ID
        ],
        ClassMetadataSearcherProxy::OPTION_ACTIVE_SEARCHER => ClassMetadataService::SERVICE_ID,
    ]
);
