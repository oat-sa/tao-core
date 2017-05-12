<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 12/05/17
 * Time: 14:25
 */

namespace oat\tao\model\mvc\middleware;


use oat\tao\model\asset\AssetService;

class TaoAssetConfiguration extends AbstractTaoMiddleware
{

    public function __invoke($request, $response, $args)
    {
        /**
         * Load external resources for the current context
         * @see tao_helpers_Scriptloader
         */
        $assetService = $this->getServiceLocator()->get(AssetService::SERVICE_ID);
        $cssFiles = [
            $assetService->getAsset('css/layout.css', 'tao'),
            $assetService->getAsset('css/tao-main-style.css', 'tao'),
            $assetService->getAsset('css/tao-3.css', 'tao')
        ];

        //stylesheets to load
        \tao_helpers_Scriptloader::addCssFiles($cssFiles);

        if(\common_session_SessionManager::isAnonymous()) {
            \tao_helpers_Scriptloader::addCssFile(
                $assetService->getAsset('css/portal.css', 'tao')
            );
        }
        return $response;
    }


}