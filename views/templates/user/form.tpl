<?php use oat\tao\helpers\Template; ?>

<div class="main-container flex-container-main-form">
    <h2><?= $formTitle; ?></h2>
    <div class="form-container"></div>
</div>

<script>
    requirejs.config({
        config : {
            'controller/users/add' : {
                formContainer : '.form-container',
                userLabel : '<?= json_encode($user); ?>'
            },
            'controller/users/edit' : {
                formContainer : '.form-container',
                user : '<?= json_encode($user); ?>'
            }
        }
    });
</script>

<?php Template::inc('footer.tpl'); ?>