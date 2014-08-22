<ul id="select-role-box" class="entry-pages-container plain">
    <?php foreach (get_data('entries') as $entry) : ?>
        <li class="entry-page-unit">
            <h1><?= $entry->getTitle() ?></h1>

            <p><?= $entry->getDescription() ?></p>

            <div class="clearfix">

                <a class="block rgt btn-info small" href="<?= $entry->getUrl() ?>"><?= $entry->getLabel() ?><span class="icon-login r"></span> </a>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
