<script type="text/javascript">
<?if(has_data('reload')):?>
require(['uiBootstrap'], function(uiBootstrap){
    uiBootstrap.initTrees();
});
<?endif?>

<?if(has_data('message')):?>
require(['helpers'], function(helpers){
    helpers.createMessage(<?=json_encode(get_data('message'))?>);
});
<?endif?>
</script>