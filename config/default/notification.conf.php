<?php
/**
 * Default config header created during install
 */
use oat\tao\model\notification\implementation\NotificationServiceAggregator;
use oat\tao\model\notification\implementation\RdsNotificationService;
return new NotificationServiceAggregator(array(
    NotificationServiceAggregator::OPTION_RDS_NOTIFICATION_SERVICE => array(
        'class' => RdsNotificationService::class,
        'options' => array(
            'persistence' => 'default',
            'visibility' => false
        )
    )
));