<?php
/**
 * Default config header created during install
 */
use oat\tao\model\notification\implementation\RdsNotificationService;

return new oat\tao\model\notification\implementation\NotificationServiceAggregator(array(
    'rds' => array(
        'class' => RdsNotificationService::class,
        'options' => array(
            'persistence' => 'default',
            'visibility' => false
        )
    )
));
