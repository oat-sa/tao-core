<?php
return new oat\tao\model\clientConfig\ClientConfigService(array(
    'configs' => array(
        'themesAvailable' => new oat\tao\model\clientConfig\sources\ThemeConfig()
    )
));
