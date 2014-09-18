<?php
use oat\tao\helpers\Layout;

$sections = get_data('sections');
?>

<?php if ($sections): ?>
    <div class="section-container" id="tabs">
        <ul class="tab-container">
            <?php foreach ($sections as $section): ?>

                <li class="small">
                    <a href="#panel-<?= $section->getId() ?>"
                       data-url="<?= $section->getUrl() ?>"
                       title="<?= $section->getName(); ?>"><?= __($section->getName()) ?></a>
                </li>

            <?php endforeach ?>
        </ul>
        <?php foreach ($sections as $section): ?>
            <div class="clear content-wrapper content-panel" id="panel-<?= $section->getId() ?>">

                <section class="navi-container">
                    <div class="section-trees">
                        <?php foreach ($section->getTrees() as $i => $tree): ?>
                            <div class="tree-block">
                                <ul id="tree-actions-<?= $i ?>"
                                    class="plain search-action-bar action-bar horizontal-action-bar">
                                    <?php foreach ($section->getActionsByGroup('search') as $action): ?>
                                        <li class="tree-search btn-info small action"
                                            data-context="<?= $action->getContext() ?>"
                                            title="<?= $action->getName() ?>"
                                            data-action="<?= $action->getBinding() ?>">
                                            <a class="li-inner" href="<?= $action->getUrl(); ?>">
                                                <?=
                                                Layout::renderIcon(
                                                    $action->getIcon(),
                                                    ' icon-magicwand'
                                                ); ?> <?= $action->getName(); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="search-form">
                                <div data-purpose="search" data-current="none" class="search-area search-search"></div>
                            </div>
                            <div class="filter-form">
                                <div class="search-area search-filter">
                                    <div class="xhtml_form">
                                        <div class="form-group">
                                            <input type="text" autocomplete="off" placeholder="<?=__('You can use * as a wildcard')?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="tree-<?= $i ?>"
                                 class="taotree taotree-<?= is_null($tree->get('className')) ? 'default' : strtolower(
                                     $tree->get('className')
                                 ) ?>"
                                 data-url="<?= $tree->get('dataUrl') ?>"
                                 data-rootNode="<?= $tree->get('rootNode') ?>"
                                 data-action-selectclass="<?= $tree->get('selectClass') ?>"
                                 data-action-selectinstance="<?= $tree->get('selectInstance') ?>"
                                 data-action-delete="<?= $tree->get('delete') ?>"
                                 data-action-moveinstance="<?= $tree->get('moveInstance') ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="tree-action-bar-box">
                        <ul class="action-bar plain tree-action-bar vertical-action-bar">
                            <?php foreach ($section->getActionsByGroup('tree') as $action): ?>
                                <li class="action"
                                    data-context="<?= $action->getContext() ?>"
                                    title="<?= $action->getName() ?>"
                                    data-action="<?= $action->getBinding() ?>">
                                    <a class="li-inner" href="<?= $action->getUrl(); ?>">
                                        <?=
                                        Layout::renderIcon(
                                            $action->getIcon(),
                                            ' icon-magicwand'
                                        ); ?> <?= $action->getName(); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <ul class="action-bar hidden">
                            <?php foreach ($section->getActionsByGroup('none') as $action): ?>
                                <li class="action" 
                                    data-context="<?= $action->getContext() ?>"
                                    data-action="<?= $action->getBinding() ?>"
                                    title="<?= $action->getName() ?>" >
                                    <a class="li-inner" href="<?= $action->getUrl(); ?>">
                                        <?= $action->getName(); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                </section>

                <section class="content-container">
                    <ul class="action-bar plain content-action-bar horizontal-action-bar">
                        <?php foreach ($section->getActionsByGroup('content') as $action): ?>
                            <li class="btn-info small action" 
                                data-context="<?= $action->getContext() ?>"
                                title="<?= $action->getName() ?>" 
                                data-action="<?= $action->getBinding() ?>">
                                <a class="li-inner" href="<?= $action->getUrl(); ?>">
                                    <?=
                                    Layout::renderIcon(
                                        $action->getIcon(),
                                        ' icon-magicwand'
                                    ); ?> <?= $action->getName(); ?>
                                </a>
                            </li>

                        <?php endforeach ?>
                    </ul>
                    <div class="content-block"></div>
                </section>

            </div>
        <?php endforeach ?>



        <aside class="meta-container">
            <div id="section-meta"></div>
        </aside>
    </div>
<?php endif; ?>
