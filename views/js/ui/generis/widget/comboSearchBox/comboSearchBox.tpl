<div class="ui-generis-widget combo-search-box">
    {{#if label}}
    <div class="label left">
        {{> ui-generis-widget-label }}
    </div>
    {{/if}}

    <div class="widget right {{#unless label}}full{{/unless}}">
        <!-- input -->
        <div class="input">
            <input
                name="{{uri}}"
                placeholder="{{placeholder}}"
                value="{{#if valueObj}}{{valueObj.label}}{{/if}}"
                data-label="{{#if valueObj}}{{valueObj.label}}{{/if}}"
                data-value="{{value}}"
                readonly="readonly">
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
                <div
                    class="item {{#equal ../value this.uri}}selected{{/equal}}"
                    data-label="{{this.label}}"
                    data-value="{{this.uri}}">
                    <span class="icon-radio not-selected"></span>
                    <span class="icon-radio-bg selected"></span>
                    {{this.label}}
                </div>
                {{/each}}
            </div>
        </div>
    </div>

</div>
