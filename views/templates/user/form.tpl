<?php
use oat\tao\helpers\Template;

$class = new core_kernel_classes_Class(CLASS_TAO_USER);
$userService = tao_models_classes_UserService::singleton();

// Label
$userLabel = $userService->createUniqueLabel($class);

?>

<div class="main-container flex-container-main-form">
    <h2><?=get_data('formTitle')?></h2>
    <div class="form-container">
        <!--<?=get_data('myForm')?>-->
    </div>
</div>

<script>
    requirejs
    .config({
        config : {
            'tao/controller/users/add' : {
                formContainer : '.form-container'
                /* exit : <?= json_encode(get_data('exit')) ?>, */
                /* loginId : <?= json_encode(get_data('loginUri')) ?> */
            }
        }
    });
</script>



<?php Template::inc('footer.tpl'); ?>
