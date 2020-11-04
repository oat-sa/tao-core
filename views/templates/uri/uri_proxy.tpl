<html>
<header>
    <script>
        const resourceId = window.location.hash ? window.location.hash.substring(1) : "";
        const resolverUrl = "<?= get_data('resolverUrl') ?>";
        const resourceUrl = "<?= get_data('resourceUrl') ?>";
        const id = resourceUrl + '#' + resourceId;

        window.location = resolverUrl + "?resourceUri=" + encodeURIComponent(id);
    </script>
</header>
<body></body>
</html>
