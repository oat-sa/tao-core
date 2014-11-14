<?php foreach (get_data('actions') as $action): ?>
        <li class="search-area search-search">
            <form action="<?= $action->getUrl(); ?>" method="post">
                <input type="text" value="" name="query" placeholder="<?=$action->getName(); ?>">
                <button class="icon-find" type="button">
                </button>
            </form>
        </li>
<?php endforeach; ?>
