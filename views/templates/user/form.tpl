<?php
use oat\tao\helpers\Template;
?>

<?php if(get_data('exit')):?>
	<script>
		window.location = "<?=_url('index', 'Main', 'tao', array('structure' => 'users', 'ext' => 'tao', 'section' => 'list_users'))?>";
	</script>
<?php else:?>
	
<div class="main-container flex-container-main-form">
    <h2><?=get_data('formTitle')?></h2>
    <div class="form-container">
        <?=get_data('myForm')?>
    </div>
</div>

<script>
    requirejs.config({
        config : {
            'tao/controller/users/add' : {
                loginId : <?=json_encode(get_data('loginUri'))?>
            }
        } 
    });		
</script>

<?php Template::inc('footer.tpl'); ?>

<?php endif?>
