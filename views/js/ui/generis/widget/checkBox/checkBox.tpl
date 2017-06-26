<div class="ui-generis-widget check-box">
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