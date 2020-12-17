<?php
/**
 * Default config header created during install
 */

/**
Example config:

return new oat\tao\model\extension\UpdatingNotificationService([
    'notifiers' => [
        [
            'notifier' => new \oat\tao\model\notifiers\OpsGenieNotifierInterface('1fc9e71d-5390-4634-a866-b727b0f7ca25'),
            'dispatchTypes' => [
                \oat\oatbox\reporting\Report::TYPE_ERROR
            ]
        ]
    ]
]);
**/

return new oat\tao\model\extension\UpdatingNotificationService([
    'notifiers' => []
]);
