<?php

/**
 * Default config header created during install
 */

/**
 Example config:

    'webhooks' => array(
        'testwh' => array(
            'id' => 'testwh',
            'url' => 'https://webhookurl',
            'httpMethod' => 'PUT',
            'retryMax' => 5,
            'responseValidatoin' => false,
            'auth' => array(
                'authClass' => '\\oat\\taoOauth\\model\\bootstrap\\OAuth2AuthType',
                'credentials' => array(
                    'client_id' => 'clent_id',
                    'client_secret' => 'client_secret',
                    'token_url' => 'https://tokenurl',
                    'token_type' => 'TT',
                    'grant_type' => 'granttype'
                )
            )
        )
    ),
    'events' => array(
        'oat\\taoProctoring\\model\\event\\DeliveryExecutionFinished' => ['testwh']
    )

 **/

return new oat\tao\model\webhooks\WebhookFileRegistry([
    'webhooks' => [
    ],
    'events' => [
    ]
]);
