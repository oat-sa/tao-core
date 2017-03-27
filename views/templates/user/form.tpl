<?php use oat\tao\helpers\Template; ?>

<div class="main-container flex-container-main-form">
    <h2><?= $formTitle; ?></h2>
    <div class="form-container"></div>
</div>

<script>
    requirejs.config({
        config : {
            'tao/controller/users/add' : {
                formContainer : '.form-container',
                user : '<?= $user; ?>',
                userLabel : '<?= $userLabel; ?>'
            }
        }
    });
</script>

<?php Template::inc('footer.tpl'); ?>