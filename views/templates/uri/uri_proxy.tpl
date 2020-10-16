<html>
<header>
    <script>
        if (window.location.hash) {
            const resourceId = window.location.hash.substring(1);
            const resolverUrl = "<?= get_data('resolverUrl') ?>";
            const resourceUrl = "<?= get_data('resourceUrl') ?>";
            const id = resourceUrl + '#' + resourceId;

            window.location = resolverUrl + "?resourceUri=" + encodeURIComponent(id);
        } else {
            window.location = "/";
        }
    </script>
</header>
<body></body>
</html>
