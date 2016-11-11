<?php

return new \oat\tao\model\mvc\view\ViewManager(
            [
                'tao' => [
                    'class' => \oat\tao\model\mvc\view\TaoViewRender::class
                ],
                'json' => [
                    'class' => \oat\tao\model\mvc\view\TaoJsonRender::class
                ],
            ]
        );

