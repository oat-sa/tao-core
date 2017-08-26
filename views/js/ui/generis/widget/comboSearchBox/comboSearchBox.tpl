<div class="ui-generis-widget combo-search-box">
    <div class="left">
        {{> ui-generis-widget-label }}
    </div>

    <div class="right">
        <!-- input -->
        <div class="input">
            <input name="delivery" placeholder="{{placeholder}}" value="" data-value="" readonly="readonly">
            <span class="icon-spinner"></span>
        </div>

        <!-- dropdown -->
        <div class="dropdown">
            <div class="search">
                <input type="text" placeholder="{{__ 'Search options...'}}">
                <span class="icon-find"></span>
            </div>
            <div class="divider"></div>
            <div class="menu">
                <div class="no-results">{{__ 'No results...'}}</div>
                {{#each range}}
                <div class="item" data-value="{{this.uri}}" data-text="{{this.label}}">
                    <span class="icon-test"></span> {{this.label}}
                </div>
                {{/each}}
            </div>
        </div>
    </div>

</div>
