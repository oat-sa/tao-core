<li class="search-area"
    title="<?= get_data('searchLabel') ?>"
    data-url="<?= _url('searchParams', 'Search', 'tao', array('rootNode' => get_data('rootNode'))) ?>">
    <input type="text" value="" name="query" placeholder="<?= get_data('searchLabel') ?>">
    <span class="search-area-buttons-container">
        <button class="icon-find" type="button"></button>
        <button class="icon-ul" type="button" title="Open results"><div class="results-counter"></div></button>
    </span>
</li>