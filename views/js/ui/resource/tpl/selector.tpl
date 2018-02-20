<div class="resource-selector {{#if multiple}}multiple{{/if}}">

    {{#if showContext}}
    <nav class="context">
        <div class="class-context"></div>
        {{#each formats}}
        <a href="#" data-view-format="{{@key}}" {{#if active}}class="active"{{/if}} {{#if title}}title="{{title}}"{{/if}}><span class="{{icon}}"></span></a>
        {{/each}}
    </nav>
    {{/if}}

    {{#if showSelection}}
    <nav class="selection">
        <div class="search">
            <input type="text" placeholder="{{searchPlaceholder}}" />
            {{# if filters }}
            <a href="#" class="filters-opener" title="{{__ 'Advanced search, add filters'}}"><span class="icon-add"></span></a>
            {{/if}}
        </div>
        <div class="selection-control">
            <a href="#" class="selection-toggle {{#unless switchMode}}hidden{{/unless}}" title="{{__ 'Toggle multiple/single selection'}}">
                <span class="icon-multi-select"></span>
            </a>
            <label class="selection-indicator {{#unless multiple}}hidden{{/unless}}">
                <input type="checkbox">
                <span class="icon-checkbox"></span>
            </label>
        </div>
        {{# if filters }}
        <div class="filters-container folded"></div>
        {{/if}}
    </nav>
    {{/if}}

    <main>
        <span class="hidden no-results">{{noResultsText}}</span>
    </main>

    {{#if multiple}}
    <footer>
        <div class="get-selection">
           <span>{{__ 'Selected'}} {{type}} : </span><span class="selected-num">0</span>
        </div>
    </footer>
    {{/if}}
</div>
