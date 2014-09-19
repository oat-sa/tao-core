<script>
<?php if(has_data('message')):?>
require(['helpers'], function(helpers){
    helpers.createMessage(<?=json_encode(get_data('message'))?>);
});
<?php endif?>
<?php if(get_data('reload')):?>
require(['jquery'], function ($) {
   $('.tree').trigger('refresh.taotree'); 
});
<?php endif;?>
</script>
