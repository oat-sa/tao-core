<div class="ui-generis-widget combo-box">
    <label for="{{uri}}">{{label}}</label>
    <select name="{{uri}}">
        <option value="">{{__ 'Choose a value...'}}</option>
        {{#each range}}
        <option value="{{uri}}">{{label}}</option>
        {{/each}}
    </select>
</div>