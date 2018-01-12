<?php
/**
 * Default config header created during install
 */

use oat\tao\model\TaoOntology;
use oat\generis\model\OntologyRdfs;

return new \oat\tao\model\search\dataProviders\OntologyDataProvider(array(
    'indexesMap' => [
        TaoOntology::CLASS_URI_ITEM => [
            'fields' => [
                'label'
            ],
        ],
        TaoOntology::CLASS_URI_TEST => [
            'fields' => [
                'label'
            ],
        ],
        TaoOntology::CLASS_URI_SUBJECT => [
            'fields' => [
                'label'
            ],
        ]
    ]
));
