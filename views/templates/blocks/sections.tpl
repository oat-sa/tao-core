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
                                      <li class="tree-search btn-info small action search-trigger">
                                          <span class="icon-find"></span>
                                          <a href=""><?= __('Find') ?></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="search-form">
                                <div id="form-container" class="ui-widget-content ui-corner-bottom">
                                    <div class="xhtml_form">
                                        <form method="post" id="form_1" name="form_1" action="/taoItems/Items/search">
                                            <input name="form_1_sent" value="1" type="hidden">

                                            <div class="form-toolbar"><a href="#" class="form-submitter"><img src="http://tao26.lan/tao/views//img/search.png">
                                                    Search</a></div>
                                            <input name="clazzUri" id="clazzUri" value=""
                                                   type="hidden">

                                            <div id="params" class="form-group">Options
                                                <div>
                                                    <div><span class="form_desc">Filtering mode</span>

                                                        <div class="form_radlst"><input name="chaining" id="chaining_0" value="or" checked="checked"
                                                                                        type="radio"><label class="elt_desc" for="chaining_0">Exclusive (OR)</label><br><input
                                                                name="chaining" id="chaining_1" value="and" type="radio"><label class="elt_desc" for="chaining_1">Inclusive
                                                                (AND)</label><br></div>
                                                    </div>
                                                    <div><span class="form_desc">Recursive</span>

                                                        <div class="form_radlst"><input name="recursive" id="recursive_0" value="0" checked="checked"
                                                                                        type="radio"><label class="elt_desc" for="recursive_0">Current class +
                                                                Subclasses</label><br><input name="recursive" id="recursive_1" value="10" type="radio"><label
                                                                class="elt_desc" for="recursive_1">Current class only</label><br></div>
                                                    </div>
                                                    <div><label class="form_desc" for="lang">Language</label><select name="lang" id="lang">
                                                            <option value="0"></option>
                                                            <option value="da-DK">Danish</option>
                                                            <option value="en-US">English</option>
                                                            <option value="fr-FR">French</option>
                                                            <option value="pt-PT">Portuguese</option>
                                                            <option value="sv-SE">Swedish</option>
                                                        </select></div>
                                                </div>
                                            </div>
                                            <div id="filters" class="form-group">Filters
                                                <div>
                                                    <div><span class="form_desc"></span><span class="form-elt-info form-elt-container">Use the * character to replace any string</span>
                                                    </div>
                                                    <div><label class="form_desc" for="">Label</label><input
                                                            name="-schema_3_label"
                                                            id="-schema_3_label" value="" type="text"></div>
                                                    <div><span class="form_desc">Original Filename</span><span class="form-elt-info form-elt-container"></span>
                                                    </div>
                                                    <div><span class="form_desc">Item Model</span>

                                                        <div class="form_radlst"><input value=""
                                                                                        name=""
                                                                                        id=""
                                                                                        type="checkbox">&nbsp;<label class="elt_desc"
                                                                                                                     for="">Open
                                                                Web Item</label><br><input value=""
                                                                                           name=""
                                                                                           id=""
                                                                                           type="checkbox">&nbsp;<label class="elt_desc"
                                                                                                                        for="">QTI</label><br>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-toolbar"><a href="#" class="form-submitter"><img src="http://tao26.lan/tao/views//img/search.png">
                                                    Search</a></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div id="tree-<?= $i ?>"
                                 class="taotree taotree-<?= is_null($tree->get('className')) ? 'default' : strtolower(
                                     $tree->get('className')
                                 ) ?>"
                                 data-url="<?= $tree->get('dataUrl') ?>"
                                 data-action-selectclass="<?= $tree->get('selectClass') ?>"
                                 data-action-selectinstance="<?= $tree->get('selectInstance') ?>"
                                 data-action-delete="<?= $tree->get('deletel') ?>"
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
                                    data-action="<?= $action->getBinding() ?>" url="">
                                    <a href="<?= $action->getUrl(); ?>">
                                        <?=
                                        Layout::renderMenuIcon(
                                            $action->getIcon(),
                                            ' icon-magicwand'
                                        ); ?> <?= $action->getName(); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <ul class="action-bar hidden">
                            <?php foreach ($section->getActionsByGroup('none') as $action): ?>
                                <li class="action" data-context="<?= $action->getContext() ?>"
                                    title="<?= $action->getName() ?>" data-action="<?= $action->getBinding() ?>">
                                    <a href="<?= $action->getUrl(); ?>">
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
                            <li class="btn-info small action" data-context="<?= $action->getContext() ?>"
                                title="<?= $action->getName() ?>" data-action="<?= $action->getBinding() ?>" url="">
                                <a href="<?= $action->getUrl(); ?>">
                                    <?=
                                    Layout::renderMenuIcon(
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
