<?php if (has_data('trees')): ?>
    <?php foreach (get_data('trees') as $i => $tree): ?>
        <div class="tree-block">
            <div id="tree-actions-<?= $i ?>" class="tree-actions">
                <div class="tree-filters">
                    <input type="text" id="filter-content-<?= $i ?>" autocomplete="off" size="10"
                           placeholder="<?= __('* = any string') ?>"/>
                    <span id="filter-action-<?= $i ?>" title="<?= __("Filter") ?>" class="icon-filter"></span>
                    <span id="filter-cancel-<?= $i ?>" title="<?= __("Remove filter") ?>" class="icon-close"></span>
                </div>
                <div class="tree-search">
                    <a href=""><?=__('Advanced')?></a>
                </div>
            </div>
            <div id="tree-<?= $i ?>"></div>
        </div>
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