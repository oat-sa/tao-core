<div class="ui-generis-widget check-box">
    <div class="left">
        {{> ui-generis-widget-label }}
    </div>
    <div class="right">
        <div class="check-box-input">
            {{#each range}}
            <div class="option">
                <label>
                    <input
                        type="checkbox"
                        name="{{this.uri}}"
                        value="{{this.uri}}"
                        {{#includes ../values this.uri}}
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
