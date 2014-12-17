<li class="search-area"
    title="<?= get_data('searchLabel') ?>"
    data-url="<?= _url('searchParams', 'Search', 'tao', array('rootNode' => get_data('rootNode'))) ?>">
        <input type="text" value="" name="query" placeholder="<?= get_data('searchLabel') ?>">
        <button class="icon-find" type="button"></button>
        <span class="icon-help tooltipstered" data-tooltip="~ .tooltip-content:first" data-tooltip-theme="info"></span>
        <div class="tooltip-content">
            <div class="grid-row" style="min-width:250px;">
                <div class="col-6">
                    <span class="icon-find"></span> = Fuzzy Matching
                </div>
                <div class="col-6">
                    <span class="icon-target"></span> = Exact Matching
                </div>
            </div>
            <hr/>
            <?php foreach (oat\tao\model\search\SearchService::getIndexesByClass(new \core_kernel_classes_Class(get_data('rootNode'))) as $uri => $indexes): ?>
                <?php foreach ($indexes as $index): ?>
                <div>
                    <span class="<?= ($index->isFuzzyMatching()) ? "icon-find" : "icon-target" ?>"></span> <?= $index->getIdentifier() ?> 
                </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
</li>