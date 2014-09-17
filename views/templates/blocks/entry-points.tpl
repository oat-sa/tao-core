<ul id="entry-point-box" class="plain">
    <?php foreach (get_data('entries') as $entry) :
        $entryUrl = \tao_helpers_Uri::_url($entry->getAction(), $entry->getController(), $entry->getExtensionId()); ?>
        <li>
            <a class="block entry-point entry-point-<?= $entry->getId() ?>"
			   href="<?= $entryUrl ?>">
                <h1><?= $entry->getTitle() ?></h1>

                <p><?= $entry->getDescription() ?></p>

                <div class="clearfix">
                    <span href="<?= $entryUrl ?>"
						  class="text-link"><span class="icon-login"></span> <?= __('Enter') ?> <?= $entry->getLabel() ?> </span>
                </div>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
