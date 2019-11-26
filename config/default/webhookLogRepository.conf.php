<?php

declare(strict_types=1);

use oat\tao\model\webhooks\log\WebhookLogRepository;

return new WebhookLogRepository(
    [WebhookLogRepository::OPTION_PERSISTENCE => 'default']
);
