<?php
/**
 * Default config header created during install
 */
use oat\tao\model\notification\implementation\RdsNotification;

return new oat\tao\model\notification\implementation\NotificationServiceAggregator(array(
    'rds' => array(
        'class' => RdsNotification::class,
        'options' => array(
            'persistence' => 'default',
            'visibility' => false
        )
    )
));
