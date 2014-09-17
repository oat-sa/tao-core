<?php if (has_data('trees')): ?>
    <?php foreach (get_data('trees') as $i => $tree): ?>
        <div class="tree-block">
            <ul id="tree-actions-<?= $i ?>" class="plain search-action-bar action-bar horizontal-action-bar">
                <li class="tree-search btn-info small action">
                    <a href=""><?=__('Filter')?><span class="icon-find r"></span></a>
                    <ul class="plain hidden">
                        <li class="tree-filters">
                            <input type="text" id="filter-content-<?= $i ?>" autocomplete="off" size="10"
                                   placeholder="<?= __('* = any string') ?>"/>
                            <span id="filter-action-<?= $i ?>" title="<?= __("Filter") ?>" class="icon-filter"></span>
                            <span id="filter-cancel-<?= $i ?>" title="<?= __("Remove filter") ?>" class="icon-close"></span>
                        </li>
                    </ul>
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
