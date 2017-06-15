<?php
/**
 * Created by PhpStorm.
 * User: siwane
 * Date: 15/06/17
 * Time: 10:14
 */

namespace oat\tao\scripts\install;


use oat\oatbox\extension\InstallAction;
use oat\tao\model\requiredAction\implementation\RequiredActionRedirectUrlPart;
use oat\tao\model\requiredAction\implementation\RequiredActionService;
use oat\tao\model\routing\Resolver;

class UpdateRequiredActionUrl extends InstallAction
{
    public function __invoke($params)
    {
        $requiredActionService = $this->getServiceManager()->get(RequiredActionService::CONFIG_ID);
        $actions = $requiredActionService->getOption(RequiredActionService::OPTION_REQUIRED_ACTIONS);
        $updated = false;
        foreach ($actions as $key => $action) {
            if (is_a($action, 'oat\tao\model\requiredAction\implementation\RequiredActionRedirect')) {
                $request = new Resolver(new \common_http_Request($action->getUrl()));
                $actions[$key] = new RequiredActionRedirectUrlPart(
                    $action->getName(),
                    $action->getRules(),
                    array(
                        $request->getMethodName(),
                        $request->getControllerShortName(),
                        $request->getExtensionId(),
                    )
                );
                $updated = true;
            }
        }

        if ($updated) {
            $requiredActionService->setOption(RequiredActionService::OPTION_REQUIRED_ACTIONS, $actions);
            $this->getServiceManager()->register(RequiredActionService::CONFIG_ID, $requiredActionService);
        }
    }

}