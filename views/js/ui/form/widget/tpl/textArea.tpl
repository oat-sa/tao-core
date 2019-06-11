<div class="form-widget {{widgetType}}">
    <div class="widget-label">
        {{> ui-form-widget-label }}
    </div>
    <div class="widget-field">
        <textarea class="widget-input" {{#if rows}}rows="{{rows}}" {{/if}}{{#if cols}}cols="{{cols}}" {{/if}}name="{{uri}}">{{value}}</textarea>
    </div>
</div>
