<? use oat\tao\helpers\Template; ?>

<div class="main-container flex-container-main-form">
    <div class="form-container"></div>
</div>

<script>
    requirejs.config({
        config: {
            'controller/users/edit': {
                uri: '<?= get_data("uri") ?>'
            }
        }
    })
</script>

<? Template::inc('footer.tpl'); ?>