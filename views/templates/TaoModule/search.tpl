<div class="" style="display: block;">
    <div class="search-area search-search" data-current="none" data-purpose="search">
    <form action="<?= get_data('requestUrl'); ?>" name="form_1" id="search_field_form_1" method="post">
        <div><input type="text" value="" name="query"></div>
        <input type="hidden" value="<?=get_data('root')?>" name="clazzUri">
        <div class="form-toolbar">
            <button class="form-submitter btn-success small" type="button">
                <span class="icon-find"></span>Search
            </button>
        </div>
    </form>
    </div>
</div>