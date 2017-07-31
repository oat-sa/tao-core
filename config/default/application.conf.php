<?php
/**
 * Default config header created during install
 */

return new oat\tao\model\mvc\Application\TaoApplication(array(
    'templates' =>
        ['tao' => [
            'preProcess' =>
                [
                    \oat\tao\model\mvc\middleware\TaoInitUser::class,
                    \oat\tao\model\mvc\middleware\LoadExtensionConstant::class,
                    \oat\tao\model\mvc\middleware\TaoRestAuthenticate::class,
                    \oat\tao\model\mvc\middleware\TaoAuthenticate::class,
                    \oat\tao\model\mvc\middleware\TaoAssetConfiguration::class,
                ],
            'process' =>
                [
                    \oat\tao\model\mvc\middleware\TaoControllerExecution::class,

                ],
            'postProcess' =>
                [
                    \oat\tao\model\mvc\middleware\ControllerRendering::class,
                ],
        ],
        ],
    'routes'   => array(),
));
