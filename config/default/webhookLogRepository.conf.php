<?php

use \oat\tao\model\webhooks\log\WebhookLogRepository;

return new WebhookLogRepository(
    [WebhookLogRepository::OPTION_PERSISTENCE => 'default']
);
