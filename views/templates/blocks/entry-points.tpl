<ul id="entry-point-box" class="plain">
    <?php foreach (get_data('entries') as $entry) : ?>
        <li>
            <a class="block entry-point entry-point-<?= $entry->getId() ?>"
			   href="<?= ROOT_URL . $entry->getExtensionId() . '/' . $entry->getController() . '/' . $entry->getAction() ?>">
                <h1><?= $entry->getTitle() ?></h1>

                <p><?= $entry->getDescription() ?></p>

                <div class="clearfix">
                    <span href="<?= ROOT_URL . $entry->getExtensionId() . '/' . $entry->getController() . '/' . $entry->getAction() ?>"
						  class="text-link"><span class="icon-login"></span> <?= __('Enter') ?> <?= $entry->getLabel() ?> </span>
                </div>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
