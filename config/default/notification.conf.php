<?php
/**
 * Default config header created during install
 */
use oat\tao\model\notification\implementation\NotificationServiceAggregator;
use oat\tao\model\notification\implementation\RdsNotificationService;
return new NotificationServiceAggregator([
    NotificationServiceAggregator::OPTION_RDS_NOTIFICATION_SERVICE => [
        'class' => RdsNotificationService::class,
        'options' => [
            'persistence' => 'default',
            'visibility' => false
        ]
    ]
]);
