<li class="search-area"
    title="<?= get_data('searchLabel') ?>"
    data-url="<?= _url('searchParams', 'Search', 'tao', array('rootNode' => get_data('rootNode'))) ?>">
        <input type="text" value="" name="query" placeholder="<?= get_data('searchLabel') ?>">
        <button class="icon-find" type="button"></button>
        <span class="icon-help tooltipstered" data-tooltip="~ .tooltip-content:first" data-tooltip-theme="info"></span>
        <div class="tooltip-content">
                <div>Tyger Tyger, burning bright,</div>
                <div>In the forests of the night;</div>
                <div>What immortal hand or eye,</div>
                <div>Could frame thy fearful symmetry? </div>
        </div>
</li>