<?php foreach (get_data('actions') as $action): ?>
    <li class="search-area"
        id="<?=$action->getId()?>"
        title="<?= $action->getName() ?>"
        data-url="<?= $action->getUrl() ?>">
            <input type="text" value="" name="query" placeholder="<?=$action->getName(); ?>">
            <button class="icon-find" type="button">
            </button>
    </li>
<?php endforeach; ?>
