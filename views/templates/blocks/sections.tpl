<?php
use oat\tao\helpers\Layout;

$sections = get_data('sections');
?>

<?php if ($sections): ?>
    <div class="section-container">
        <ul class="tab-container">
            <?php foreach ($sections as $section): ?>

                <li class="small">
                    <a href="#panel-<?= $section->getId() ?>"
                       data-url="<?= $section->getUrl() ?>"
                       title="<?= $section->getName(); ?>"><?= __($section->getName()) ?></a>
                </li>

                <section class="content-container">
                    <ul class="action-bar plain content-action-bar horizontal-action-bar">
                        <?php foreach ($section->getActionsByGroup('content') as $action): ?>
                            <li class="btn-info small action" data-context="<?= $action->getContext() ?>"
                                title="<?= $action->getName() ?>" data-action="<?= $action->getBinding() ?>" url="">
                                <a href="<?= $action->getUrl(); ?>">
                                    <?= Layout::renderMenuIcon(
                                        $action->getIcon(),
                                        ' icon-magicwand'
                                    ); ?> <?= $action->getName(); ?>
                                </a>
                            </li>

        <section class="content-container">
            <ul class="action-bar plain content-action-bar horizontal-action-bar">
            <?php foreach ($section -> getActionsByGroup('content') as $action): ?>
                <li class="btn-info small action" data-context="<?= $action -> getContext() ?>" title="<?= $action -> getName() ?>" data-action="<?= $action -> getBinding() ?>" url="">
                    <a href="<?= $action -> getUrl(); ?>">
                        <?= Layout::renderMenuIcon($action -> getIcon(), ' icon-magicwand'); ?> <?= $action -> getName(); ?>
                    </a>
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
                                    <li class="tree-filters">
                                        <input type="text" id="filter-content-<?= $i ?>" autocomplete="off" size="10"
                                               placeholder="<?= __('* = any string') ?>"/>
                                        <span id="filter-action-<?= $i ?>" title="<?= __("Filter") ?>"
                                              class="icon-filter"></span>
                                        <span id="filter-cancel-<?= $i ?>" title="<?= __("Remove filter") ?>"
                                              class="icon-close"></span>
                                    </li>
                                    <li class="tree-search btn-info small action">
                                        <a href=""><?= __('Search') ?><span class="icon-find r"></span></a>
                                    </li>
                                </ul>
                            </div>
                            <div id="tree-<?= $i ?>"
                                 class="taotree taotree-<?= is_null($tree->get('className')) ? 'default' : strtolower(
                                     \tao_helpers_Display::textCleaner($tree->get('className'))
                                 ) ?>"
                                 data-url="<?= $tree->get('dataUrl') ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="tree-action-bar-box">
                        <ul class="action-bar plain tree-action-bar vertical-action-bar">
                            <?php foreach ($section->getActionsByGroup('tree') as $action): ?>
                                <li class="action"
                                    data-context="<?= $action->getContext() ?>"
                                    title="<?= $action->getName() ?>"
                                    data-action="<?= $action->getBinding() ?>" url="">
                                    <a href="<?= $action->getUrl(); ?>">
                                        <?= Layout::renderMenuIcon(
                                            $action->getIcon(),
                                            ' icon-magicwand'
                                        ); ?> <?= $action->getName(); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>

                <section class="content-container">
                    <ul class="action-bar plain content-action-bar horizontal-action-bar">
                        <?php foreach ($section->getActionsByGroup('content') as $action): ?>
                            <li class="btn-info small action" data-context="<?= $action->getContext() ?>"
                                title="<?= $action->getName() ?>" data-action="<?= $action->getBinding() ?>" url="">
                                <a href="<?= $action->getUrl(); ?>">
                                    <?= Layout::renderMenuIcon(
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



    </div>
<?php endif; ?>
