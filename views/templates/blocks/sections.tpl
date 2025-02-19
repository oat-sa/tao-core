<?php
use oat\tao\helpers\Template;
use oat\tao\helpers\Layout;

$xsrfTokenName = get_data('xsrf-token-name');
$sections = get_data('sections');
?>

<?php if ($sections): ?>
    <div class="section-container">
        <?php if(Layout::isQuickWinsDesignEnabled()): ?>
        <div class="main-menu__submenu">
            <ul class="tab-container clearfix">
                <?php foreach ($sections as $section): ?>
                    <li class="small <?php if($section->getDisabled()):?>disabled<?php endif?> action">
                        <a href="#panel-<?= $section->getId() ?>"
                           data-url="<?= $section->getUrl() ?>"
                           title="<?= $section->getName(); ?>"><?= __($section->getName()) ?></a>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
        <?php else: ?>
            <ul class="tab-container clearfix">
                <?php foreach ($sections as $section): ?>

                    <li class="small <?php if($section->getDisabled()):?>disabled<?php endif?>">
                        <a href="#panel-<?= $section->getId() ?>"
                           data-url="<?= $section->getUrl() ?>"
                           title="<?= $section->getName(); ?>"><?= __($section->getName()) ?></a>
                    </li>

                <?php endforeach ?>
            </ul>
        <?php endif; ?>

        <?php foreach ($sections as $section): ?>
            <div class="hidden clear content-wrapper content-panel" id="panel-<?= $section->getId() ?>">
                <?php if(count($section->getTrees()) > 0): ?>
                <section class="navi-container">
                    <div class="section-trees">
                        <?php foreach ($section->getTrees() as $tree): ?>
                            <?php if(!Layout::isQuickWinsDesignEnabled()): ?>
                            <div class="tree-block">
                                <div class="plain action-bar horizontal-action-bar"></div>
                            </div>
                            <?php endif; ?>

                            <div id="tree-<?= $section->getId() ?>"
                                 class="taotree taotree-<?= is_null($tree->get('className'))
                                     ? 'default'
                                     : strtolower(str_replace(' ', '_', $tree->get('className'))) ?>"
                                 data-type="<?= $tree->get('type') ?>"
                                 data-url="<?= $tree->get('dataUrl') ?>"
                                 data-rootNode="<?= $tree->get('rootNode') ?>"
                                 data-icon="<?= is_null($tree->get('className')) ? 'test'  : strtolower(str_replace(' ', '-', $tree->get('className'))) ?>"
                                 data-actions="<?= htmlspecialchars(json_encode($tree->getActions()), ENT_QUOTES) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="tree-action-bar-box">
                        <ul class="plain action-bar tree-action-bar vertical-action-bar">
                        <?php
                            Template::inc('blocks/actions.tpl', 'tao', array(
                                'actions' => $section->getActionsByGroup('tree')
                            ));
                        ?>
                        </ul>
                        <ul class="hidden action-bar">
                        <?php
                            Template::inc('blocks/actions.tpl', 'tao', array(
                                'actions' => $section->getActionsByGroup('none')
                            ));
                        ?>
                        </ul>
                    </div>
                <?php endif; ?>
                </section>

                <section class="content-container">
                    <?php if(Layout::isQuickWinsDesignEnabled()): ?>
                    <div class="main-menu__submenu">
                    <?php endif; ?>
                        <ul class="plain action-bar content-action-bar horizontal-action-bar">
                            <?php
                                Template::inc('blocks/actions.tpl', 'tao', array(
                                    'action_classes' => 'btn-info small',
                                    'actions' => $section->getActionsByGroup('content')
                                ));
                            ?>
                            <?php
                                foreach ($section->getTrees() as $i => $tree) {
                                    $node = null;
                                    if (!is_null($tree->get('searchNode'))) {
                                        $node = $tree->get('searchNode');
                                    } else if (!is_null($tree->get('rootNode'))) {
                                        $node = $tree->get('rootNode');
                                    }
                                    if ($node) {
                                        Template::inc('blocks/search.tpl', 'tao', array(
                                        'rootNode' => $node,
                                        'searchLabel' => __('Search %s', $tree->get('className'))
                                        ));
                                    }
                                }
                            ?>
                        </ul>
                    <?php if(Layout::isQuickWinsDesignEnabled()): ?>
                    </div>
                    <?php endif; ?>
                    <div class="content-block"></div>

                </section>

            </div>
        <?php endforeach ?>

        <aside class="meta-container">
            <div id="section-meta"></div>
        </aside>
    </div>
<?php endif; ?>
