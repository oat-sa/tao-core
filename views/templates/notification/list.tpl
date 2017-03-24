<?php
use oat\tao\helpers\Template;
?>
<div class="data-container-wrapper flex-container-full">
    <div>
        <div class="grid-row">
            <div class="col-4">
                <h2><?= __('Notifications') ?></h2>
                <div class="form-content">
                    <table summary="modules" class="matrix">
                        <thead>
                        <tr>
                            <th class="bordered"><?= __('Date'); ?></th>
                            <th class="bordered author"><?= __('From'); ?></th>
                            <th class="version"><?= __('Title'); ?></th>
                            <th class="version"><?= __('Message'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach(get_data('notif-list') as $notification): ?>
                            <?php if($notification->getStatus() === oat\tao\model\notification\NotificationInterface::CREATED_STATUS): ?>
                                <tr >
                                    <td class="bordered"><strong><?= date( 'Y-m-d H:i' ,$notification->getCreatedAt()); ?></strong></td>
                                    <td><strong><?=  $notification->getSenderName(); ?></strong></td>
                                    <td><strong><?= $notification->getTitle(); ?></strong></td>
                                    <td><strong><?= $notification->getMessage(); ?></strong></td>
                                </tr>
                            <?php else: ?>
                                <tr >
                                    <td class="bordered"><em><?= date( 'Y-m-d H:i' ,$notification->getCreatedAt()); ?></em></td>
                                    <td><em><?=  $notification->getSenderName(); ?></em></td>
                                    <td><em><?= $notification->getTitle(); ?></em></td>
                                    <td><em><?= $notification->getMessage(); ?></em></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>