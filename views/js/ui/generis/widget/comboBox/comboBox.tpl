<div class="ui-generis-widget combo-box">
    <div class="left">
        {{> ui-generis-widget-label }}
    </div>
    <div class="right">
        <select name="{{uri}}">
            <option value="">{{__ 'Choose a value...'}}</option>
            {{#each range}}
            <option value="{{this.uri}}">{{this.label}}</option>
            {{/each}}
        </select>
    </div>
</div>
