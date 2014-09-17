<div class="main-container" data-tpl="tao/list/index.tpl">
    <h2><?= __('Create a list') ?></h2>

    <div class="form-content">
        <?= get_data('form') ?>
    </div>
</div>
<div class="data-container-wrapper">
    <?php foreach (get_data('lists') as $i => $list): ?>
        <div id='list-data_<?= $list['uri'] ?>' class="data-container">
            <div class="ui-widget ui-state-default ui-widget-header ui-corner-top container-title">
                <?= $list['label'] ?>
            </div>
            <div class="ui-widget ui-widget-content container-content" id='list-elements_<?= $list['uri'] ?>'>
                <ol>
                    <?php foreach ($list['elements'] as $level => $element): ?>
                        <li id="list-element_<?= $level ?>">
                            <span class="list-element"
                                  id="list-element_<?= $level ?>_<?= $element['uri'] ?>"><?= $element['label'] ?></span>
                        </li>
                    <?php endforeach ?>
                </ol>
            </div>
            <div class="ui-widget ui-widget-content ui-state-default ui-corner-bottom clearfix data-container-footer <?php !$list['editable'] && print 'hidden'?>">
                <?php if ($list['editable']): ?>
                    <button type="button" title="<?= __('Edit this list') ?>" class="list-edit-btn btn-info small square rgt" data-uri="<?= $list['uri'] ?>">
                        <span class="icon-edit"></span>
                    </button>
                    <button type="button" title="<?= __('Delete this list') ?>" class="list-delete-btn btn-warning small square rgt" data-uri="<?= $list['uri'] ?>">
                        <span class="icon-bin"></span>
                    </button>

                <?php endif ?>
            </div>
        </div>
    <?php endforeach ?>
</div>
