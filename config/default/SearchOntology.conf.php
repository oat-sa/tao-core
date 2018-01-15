<?php
/**
 * Default config header created during install
 */

use oat\tao\model\TaoOntology;
use oat\tao\model\search\dataProviders\DataProvider;
use oat\tao\model\search\dataProviders\OntologyDataProvider;

return new OntologyDataProvider(array(
    DataProvider::INDEXES_MAP_OPTION => [
        TaoOntology::CLASS_URI_ITEM => [
            DataProvider::FIELDS_OPTION => [
                'label'
            ],
        ],
        TaoOntology::CLASS_URI_TEST => [
            DataProvider::FIELDS_OPTION => [
                'label'
            ],
        ],
        TaoOntology::CLASS_URI_SUBJECT => [
            DataProvider::FIELDS_OPTION => [
                'label'
            ],
        ]
    ]
));
