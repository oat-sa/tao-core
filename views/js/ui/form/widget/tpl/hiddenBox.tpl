<div class="form-widget {{widgetType}}">
    <div class="widget-label">
        {{> ui-form-widget-label }}
    </div>
    <div class="widget-field">
        <input class="widget-input-inline" type="password" name="{{uri}}" />
    </div>
</div>

<div class="form-widget {{widgetType}} confirmation">
    <div class="widget-label">
        {{> ui-form-widget-label confirmation}}
    </div>
    <div class="widget-field">
        <input class="widget-input-inline" type="password" name="{{confirmation.uri}}" />
    </div>
</div>
