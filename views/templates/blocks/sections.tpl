<?php if (get_data('sections')): ?>
<div class="section-container" id="tabs">
    <ul class="tab-container">
        <?php foreach (get_data('sections') as $section): ?>
            <li class="small">
                <a
                    id="<?= $section['id'] ?>"
                    href="<?= ROOT_URL . ltrim($section['url'], '/') ?>"
                    title="<?= $section['name'] ?>"
                    data-trees="<?= json_encode($section['trees']) ?>"
                    data-actions="<?= json_encode($section['actions']) ?>"><?= __($section['name']) ?></a></li>

        <?php endforeach ?>
    </ul>

    <div class="clear">

        <section class="navi-container">
            <div id="section-trees"></div>
            <!--div id="section-actions"></div-->

            <h3 class="block-title"><?=__('Actions')?></h3>
            <ul class="action-bar plain tree-action-bar">
                <li class="action [opt: disabled|hidden]" data-context="*uri|class (context)" title="add repository (name)" data-action="instanciate (js)" url="">
                    <a href="/tao/SettingsVersioning/addInstance (url)"><span class="icon-email (icon)"></span> Action (display)</a>
                </li>
                <li class="action">
                    <a href="#"><span class="icon-email"></span> Action 2</a>
                </li>
                <li class="action">
                    <a href="#"><span class="icon-email"></span> Action 4</a>
                </li>
                <li class="action">
                    <a href="#"><span class="icon-email"></span> Action 4</a>
                </li>
                <li class="action">
                    <a href="#"><span class="icon-email"></span> Action 4</a>
                </li>
                <li class="action">
                    <a href="#"><span class="icon-email"></span> Action 4</a>
                </li>
                <li class="action">
                    <a href="#"><span class="icon-email"></span> Action 4</a>
                </li>
                <li class="action">
                    <a href="#"><span class="icon-email"></span> Action 4</a>
                </li>
            </ul>
        </section>

        <section class="content-container">
            <ul class="action-bar plain content-action-bar horizontal-action-bar">
                <li class="btn-info small action">
                    <a href="#"><span class="icon-email"></span> Action 3</a>
                </li>
                <li class="btn-info small action">
                    <a href="#"><span class="icon-email"></span> Action 4</a>
                </li>
                <li class="btn-info small action">
                    <a href="#"><span class="icon-email"></span> Action 4</a>
                </li>
                <li class="btn-info small action">
                    <a href="#"><span class="icon-email"></span> Action 4</a>
                </li>

            </ul>
            <?php foreach (get_data('sections') as $section): ?>
                <div id="<?= str_replace(' ', '_', $section['name']) ?>"></div>
            <?php endforeach ?>
        </section>

        <aside class="meta-container">
            <div id="section-meta"></div>
        </aside>
    </div>


</div>
<?php endif; ?>