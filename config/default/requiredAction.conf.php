<?php

use oat\tao\model\requiredAction\implementation\RequiredActionService;
use oat\tao\model\requiredAction\implementation\RequiredActionRedirect;
use oat\tao\model\requiredAction\implementation\CodeOfConductRule;

return  new RequiredActionService([
    RequiredActionService::OPTION_REQUIRED_ACTIONS => [
        new RequiredActionRedirect(
            'codeOfConduct',
            [
                new CodeOfConductRule(new \DateInterval('P1Y')),
            ],
            _url('codeofconduct', 'RequiredAction', 'tao')
        ),
    ]
]);
