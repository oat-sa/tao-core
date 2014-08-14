<div id="login-box">
    <h1><?=__('Connect to the TAO platform')?></h1>
    <?= get_data('form') ?>
</div>
<script>
    requirejs.config({
        config: {
            'login': {
                'info': <?= has_data('msg') ? json_encode(get_data('msg')) : '""'?>,
                'error': <?= get_data('errorMessage') ? json_encode(urldecode(get_data('errorMessage'))) : '""'?>
            }
        }
    });
</script>