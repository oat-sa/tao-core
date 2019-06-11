<div class="form-widget {{widgetType}}">
    <div class="widget-label">
        {{> ui-form-widget-label }}
    </div>
    <div class="widget-field">
        <div class="radio-box-input">
            {{#each range}}
            <div class="option">
                <label>
                    <input
                        class="widget-input-inline"
                        type="radio"
                        name="{{../uri}}"
                        value="{{this.uri}}"
                        {{#includes ../value this.uri}}
                        checked="true"
                        {{/includes}}>
                    <span class="icon-radio"></span>
                    {{this.label}}
                </label>
            </div>
            {{/each}}
        </div>
    </div>
</div>
