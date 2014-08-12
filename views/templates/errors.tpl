<? if (get_data('errorMessage')): ?>
    <script>
        callbackMeWhenReady.loginError = function () {
            helpers.createErrorMessage("<?=get_data('errorMessage')?>");
        };
    </script>
<? endif ?>