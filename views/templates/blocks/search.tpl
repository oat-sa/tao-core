<li class="search-area"
    title="<?= get_data('searchLabel') ?>"
    data-url="<?= _url('searchParams', 'Search', 'tao', array('rootNode' => get_data('rootNode'))) ?>">
        <input type="text" value="" name="query" placeholder="<?= get_data('searchLabel') ?>">
        <button class="icon-find" type="button">
        </button>
</li>