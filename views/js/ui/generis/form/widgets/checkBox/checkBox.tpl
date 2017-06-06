<div class="ui-generis-form-widgets">
    <label for="{{uri}}">{{label}}</label>

    <div class="checkbox">
        {{#each range}}
        <div class="option">
            <input type="checkbox" name="{{uri}}" value="{{uri}}">
            <label for="{{uri}}">{{label}}</label>
        </div>
        {{/each}}
    </div>
</div>