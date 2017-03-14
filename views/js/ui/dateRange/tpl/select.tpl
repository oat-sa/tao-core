<div class="daterange">
    <label>{{__ "From"}}</label><input type="text" name="periodStart" value="{{startDate}}"/>
    <label>{{__ "to"}}</label><input type="text" name="periodEnd" value="{{endDate}}"/>
    <button class="small btn-info" data-control="filter" title="{{ __ 'Apply date range' }}">
        <span class="icon icon-filter"></span> {{ __ 'Apply' }}
    </button>
    <button class="small" data-control="reset">
        <span class="icon icon-reset"></span> {{ __ 'Reset' }}
    </button>
</div>
