<div class="form-widget {{widgetType}}">
    <div class="widget-label">
        {{> ui-form-widget-label }}
    </div>
    <div class="widget-field">
        <div class="check-box-input">
            {{#each range}}
            <div class="option">
                <label>
                    <input
                        class="widget-input-inline"
                        type="checkbox"
                        name="{{this.uri}}"
                        value="{{this.uri}}"
                        {{#includes ../value this.uri}}
                        checked="true"
                        {{/includes}}>
                    <span class="icon-checkbox"></span>
                    {{this.label}}
                </label>
            </div>
            {{/each}}
        </div>
    </div>
</div>
