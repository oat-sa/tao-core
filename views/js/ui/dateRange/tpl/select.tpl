<div class="daterange">
    <span class="start">
        <label for="periodStart">{{__ "From"}}</label>
    </span>
    <span class="end">
        <label for="periodEnd">{{__ "to"}}</label>
    </span>
    {{#if applyButton.enable}}
    <button class="small btn-info" data-control="filter" title="{{applyButton.title}}">
        <span class="icon icon-filter"></span> {{applyButton.label}}
    </button>
    {{/if}}
    {{#if resetButton.enable}}
    <button class="small" data-control="reset" title="{{resetButton.title}}">
        <span class="icon icon-reset"></span> {{resetButton.label}}
    </button>
    {{/if}}
</div>
