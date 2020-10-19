<html>
<header>
    <script>
        const resourceId = window.location.hash ? window.location.hash.substring(1) : "";
        const resolverUrl = "<?= get_data('resolverUrl') ?>";
        const resourceUrl = "<?= get_data('resourceUrl') ?>";
        const id = resourceUrl + '#' + resourceId;

        fetch(resolverUrl + "?resourceUri=" + encodeURIComponent(id))
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                console.log(data);
                window.location = data.data.url;
            })
            .catch((err) => {
                alert("ERROR! " + err)
            });
    </script>
</header>
<body></body>
</html>
