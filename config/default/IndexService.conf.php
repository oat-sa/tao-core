<?php
/**
 * Default config header created during install
 */
use oat\tao\model\TaoOntology;
use oat\tao\model\search\index\IndexService;

return new oat\tao\model\search\index\IndexService([
    IndexService::INDEX_MAP_PROPERTY => [
        IndexService::INDEX_MAP_PROPERTY_DEFAULT => [
            'label',
            'content'
        ],
        IndexService::INDEX_MAP_PROPERTY_FUZZY => [
            'label',
            'content'
        ]
    ]
]);
