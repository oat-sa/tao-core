<?php if (has_data('breadcrumbs')): ?>
<ul class="breadcrumbs plain action-bar horizontal-action-bar">
    <?php foreach(get_data('breadcrumbs') as $breadcrumb): ?>
    <?php if($breadcrumb): ?>
    <li class="breadcrumb" data-breadcrumb="<?= $breadcrumb['id']; ?>">
        <?php if (isset($breadcrumbs['url'])): ?>
        <a href="<?= $breadcrumb['url']; ?>"><?= $breadcrumb['label']; ?><?= isset($breadcrumb['data']) ?' - ' . $breadcrumb['data'] : ''; ?></a>
        <?php else: ?>
        <span class="a"><?= $breadcrumb['label']; ?><?= isset($breadcrumb['data']) ?' - ' . $breadcrumb['data'] : ''; ?></span>
        <?php endif; ?>
        <?php if (isset($breadcrumb['entries'])): ?>
        <ul class="entries plain">
            <?php foreach($breadcrumb['entries'] as $entry): ?>
            <li data-breadcrumb="<?= $entry['id']; ?>">
                <a href="<?= $entry['url']; ?>"><?= $entry['label']; ?><?= isset($entry['data']) ?' - ' . $entry['data'] : ''; ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </li>
    <?php endif; ?>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
