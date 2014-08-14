        <?php if (get_data('sections')): ?>

            <div id="tabs" class="grid-box">
                <ul class="col-12">
                    <?php foreach (get_data('sections') as $section): ?>
                        <li><a id="<?= $section['id'] ?>" href="<?= ROOT_URL . substr($section['url'], 1) ?>"
                               title="<?= $section['name'] ?>"><?= __($section['name']) ?></a></li>
                    <?php endforeach ?>
                </ul>

                <div class="panels grid-box">
                    <div id="sections-aside" class="col-2">
                        <div id="section-trees"></div>
                        <div id="section-actions"></div>
                    </div>
                    <div id="section-meta" class="col-10"></div>
                </div>
            </div>
        <?php endif; ?>