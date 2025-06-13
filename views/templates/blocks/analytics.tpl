<script>
    window.initGoogleAnalytics = function() {
        const gaTag = <?= json_encode(get_data('gaTag')) ?>;
        const script = document.createElement("script");
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtag/js?id=${gaTag}`;
        document.head.appendChild(script);
        script.onload = function() {
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());

            gtag('config', gaTag, { environment: <?= json_encode(get_data('environment')) ?> });
        };
    };
</script>