<?php
use oat\tao\helpers\Template;
use oat\tao\helpers\Layout;
?>
<header class="dark-bar clearfix">
    
    <?=Layout::renderThemingTemplate('header-logo')?>
    
    <?php /* main navigation bar */
    !common_session_SessionManager::isAnonymous()
        ? Template::inc('blocks/main-navi.tpl', 'tao')
        : '';
    ?>

</header>