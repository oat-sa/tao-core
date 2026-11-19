<a class="button" href="#" data-control="kiosked-exit" hidden><?= __('Exit') ?></a>
<script>
    try {
        const exitButton = document.querySelector('[data-control="kiosked-exit"]');
        const homeButton = document.querySelector('[data-control="error-home"]');
        const navigation = document.querySelector('.plain.links');

        if (window.kiosked && typeof window.kiosked.exit === 'function') {
            if (navigation) {
                navigation.hidden = true;
            }
            if (homeButton) {
                homeButton.hidden = true;
            }
            exitButton.hidden = false;
            exitButton.addEventListener('click', event => {
                event.preventDefault();
                window.kiosked.exit();
            });
        }
    } catch (error) {
        // Keep the error page usable when the native API cannot be accessed.
    }
</script>
