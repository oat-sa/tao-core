<div class="resource-selector">

    <nav class="context">
        <div class="class-context"></div>
        {{#each formats}}
        <a href="#" data-view-format="{{@key}}" {{#if active}}class="active"{{/if}} {{#if title}}title="{{title}}"{{/if}}><span class="{{icon}}"></span></a>
        {{/each}}
    </nav>

    <nav class="selection">
        <div class="search">
            <input type="text" placeholder="{{searchPlaceholder}}" />
            {{# if filters }}
            <a href="#" class="filters-opener" title="{{__ 'Advanced search, add filters'}}"><span class="icon-add"></span></a>
            {{/if}}
        </div>
        <div class="selection-control">
            <label>
                <input type="checkbox" />
                <span class="icon-checkbox"></span>
            </label>
        </div>
        {{# if filters }}
        <div class="filters-container folded"></div>
        {{/if}}
    </nav>

    <main>

        <span class="hidden no-results">{{noResultsText}}</span>
    </main>
    <footer>
        <div class="get-selection">
           <span>{{__ 'Selected'}} {{type}} : </span><span class="selected-num">0</span>
        </div>
    </footer>
</div>
