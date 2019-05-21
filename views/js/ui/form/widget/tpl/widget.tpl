<div class="form-widget">
    <div class="widget-label">
        {{> ui-form-widget-label }}
    </div>
    <div class="widget-field">
        <input {{#if type}}type="{{type}}" {{/if}}name="{{uri}}" value="{{value}}" />
    </div>
</div>
