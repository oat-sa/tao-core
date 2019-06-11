<div class="form-widget {{widgetType}}">
    <div class="widget-label">
        {{> ui-form-widget-label }}
    </div>
    <div class="widget-field">
        <select class="widget-input-inline" name="{{uri}}">
            <option value="">{{__ 'Choose a value...'}}</option>
            {{#each range}}
            <option value="{{this.uri}}">{{this.label}}</option>
            {{/each}}
        </select>
    </div>
</div>
