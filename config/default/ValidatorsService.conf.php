<?php

use oat\tao\helpers\form\ValidationRuleRegistry;
use oat\tao\model\service\ValidatorsService;

return new ValidatorsService(
    [ValidatorsService::VALIDATION_RULE_REGISTRY_PARAM => ValidationRuleRegistry::getRegistry()]
);
