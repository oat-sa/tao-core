<div class="ui-generis-form-widgets">
    <label for="{{uri}}">{{label}}</label>
    <select name="{{uri}}">
        <option value="">{{__ 'Choose a value...'}}</option>
        {{#each range}}
        <option value="{{uri}}">{{label}}</option>
        {{/each}}
    </select>
</div>