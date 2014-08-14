<?php if (get_data('sections')): ?>

    <div id="tabs">
        <ul>
            <?php foreach (get_data('sections') as $section): ?>
                <li><a
                        id="<?= $section['id'] ?>"
                        href="<?= ROOT_URL . ltrim($section['url'], '/') ?>"
                        title="<?= $section['name'] ?>"
                        data-trees="<?= json_encode($section['trees']) ?>"
                        data-actions="<?= json_encode($section['actions']) ?>"><?= __($section['name']) ?></a></li>

            <?php endforeach ?>
        </ul>

            <div id="tree-container">
                <div id="section-trees"></div>
                <div id="section-actions"></div>
            </div>
            <div id="section-meta"></div>
    </div>
<?php endif; ?>