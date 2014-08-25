<div id="login-box" class="entry-point entry-point-container">
    <h1><?=__('Connect to the TAO platform')?></h1>
    <?= get_data('form') ?>
</div>
<script>
    requirejs.config({
        config: {
            'controller/login': {
                'message' : {
                    'info': <?=json_encode(get_data('msg'))?>,
                    'error': <?=json_encode(urldecode(get_data('errorMessage')))?>
                }
            }
        }
    });
</script>
