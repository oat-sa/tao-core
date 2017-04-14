<div class="resource-selector">

    <nav class="context">
        <div class="class-context"></div>
        {{#each formats}}
        <a href="#" data-view-format="{{@key}}" {{#if active}}class="active"{{/if}} {{#if title}}title="{{title}}"{{/if}}><span class="{{icon}}"></span></a>
        {{/each}}
    </nav>

    <nav class="selection">
        <div class="search">
            <input type="text" placeholder="{{__ 'Search'}}" />
        </div>
        <div class="selection-control">
            <label>
                <input type="checkbox" />
                <span class="icon-checkbox"></span>
            </label>
        </div>
    </nav>

    <main></main>
    <footer>
        <div class="get-selection">
           <span>{{__ 'Selected'}} {{type}} : </span><span class="selected-num">0</span>
        </div>
    </footer>
</div>
