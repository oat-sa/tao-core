<div class="resource-selector">
    <nav class="search">
        <input type="text" placeholder="{{__ 'Search'}}" />
    </nav>
    <nav class="context">
        <div class="class-context"></div>
        {{#each formats}}
        <a href="#" data-view-format="{{@key}}" {{#if active}}class="active"{{/if}} {{#if title}}title="{{title}}"{{/if}}><span class="{{icon}}"></span></a>
        {{/each}}
    </nav>
    <main></main>
    <footer>
        <a href="#" class="get-selection"><span class="selected-num">0</span> {{type}} {{__ 'selected'}}</a>
    </footer>
</div>
