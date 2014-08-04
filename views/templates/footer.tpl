<script type="text/javascript">
<?php if(has_data('message')):?>
require(['helpers'], function(helpers){
    helpers.createMessage(<?=json_encode(get_data('message'))?>);
});
<?php endif?>
<?php if(get_data('reload')):?>
require(['uiBootstrap'], function (uiBootstrap) {
    uiBootstrap.initTrees();
});
<?php endif;?>
</script>