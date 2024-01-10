<script async src="https://www.googletagmanager.com/gtag/js?id=<?= get_data('gaTag') ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', '<?= get_data('gaTag') ?>', {
        environment: '<?= get_data('environment') ?>'
    });
</script>
