<?php
use oat\tao\helpers\Layout;

$sections = get_data('sections');
?>

<?php if ($sections): ?>
<div class="section-container">
    <ul class="tab-container">
        <?php foreach ($sections as $section): ?>

            <li class="small">
                <a href="#panel-<?= $section -> getId() ?>" title="<?= $section -> getName(); ?>" data-trees="<?= json_encode($section -> getTrees()) ?>" data-actions="<?= json_encode(count($section -> getActions())) ?>"><?= __($section -> getName()) ?></a>
            </li>

        <?php endforeach ?>
    </ul>
    <?php foreach ($sections as $section): ?>
        <div class="clear content-wrapper content-panel" id="panel-<?= $section -> getId() ?>">

        <section class="navi-container">
            <div class="section-trees">
                <?php if (has_data('trees')): ?>
                    <?php foreach (get_data('trees') as $i => $tree): ?>
                    <div class="tree-block">
                        <ul id="tree-actions-<?= $i ?>" class="plain search-action-bar action-bar horizontal-action-bar">
                            <li class="tree-filters">
                                <input type="text" id="filter-content-<?= $i ?>" autocomplete="off" size="10"
                                       placeholder="<?= __('* = any string') ?>"/>
                                <span id="filter-action-<?= $i ?>" title="<?= __("Filter") ?>" class="icon-filter"></span>
                                <span id="filter-cancel-<?= $i ?>" title="<?= __("Remove filter") ?>" class="icon-close"></span>
                            </li>
                            <li class="tree-search btn-info small action">
                                <a href=""><?=__('Search')?><span class="icon-find r"></span></a>
                            </li>
                    </div>
                    <div id="tree-<?= $i ?>"></div>
                    </ul>
                <?php endforeach; ?>

                    <script>
                        requirejs.config({
                            config: {
                                'tao/controller/main/trees': {
                                    'sectionTreesData': <?=json_encode(get_data('trees'))?>
                                }
                            }
                        });
                    </script>
                <?php endif ?>
            </div>

            <h3 class="block-title"><?=__('Actions')?></h3>
            <ul class="action-bar plain tree-action-bar">
                <?php foreach ($section -> getActionsByGroup('tree') as $action): ?>
                    <li class="action" data-context="<?= $action -> getContext() ?>" title="<?= $action -> getName() ?>" data-action="<?= $action -> getBinding() ?>" url="">
                        <a href="<?= $action -> getUrl(); ?>">
                            <?= Layout::renderMenuIcon($action -> getIcon(), ' icon-magicwand'); ?> <?= $action -> getName(); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

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
            <div id="<?= ''/*str_replace(' ', '_', $section['name'])*/ ?>"></div>
        </section>

    </div>
    <?php endforeach ?>



    <aside class="meta-container">
        <div id="section-meta"></div>
    </aside>
</div>
<?php endif; ?>