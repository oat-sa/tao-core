<div class="form-widget {{widgetType}}">
    <div class="widget-label">
        {{> ui-form-widget-label }}
    </div>
    <div class="widget-field">
        <input class="widget-input-inline" {{#if type}}type="{{type}}" {{/if}}name="{{uri}}" value="{{value}}" />
    </div>
</div>
