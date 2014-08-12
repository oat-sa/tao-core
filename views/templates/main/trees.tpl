<?
if (has_data('trees')):
    foreach (get_data('trees') as $i => $tree):
        ?>
        <div class="tree-block">
            <div id="tree-actions-<?= $i ?>" class="tree-actions">
                <input type="text" id="filter-content-<?= $i ?>" autocomplete='off' size="10"
                       placeholder="<?= __('* can replace any string') ?>"/>
                <input type='button' id="filter-action-<?= $i ?>" value="<?= __("Filter") ?>"/>
                <input type='button' id="filter-cancel-<?= $i ?>" value="<?= __("Finish") ?>"
                       class="ui-helper-xhidden ui-state-error"/>
            </div>
            <div id="tree-<?= $i ?>"></div>
        </div>
    <? endforeach ?>

<script>
    requirejs.config({
        config: {
            'tao/controller/main/trees': {
                'sectionTreesData': <?=json_encode(get_data('trees'))?>
            }
        }
    });
</script>
<? endif ?>