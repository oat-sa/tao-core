{{#equal type 'class'}}
<li data-uri="{{uri}}" class="class{{#if selectable}} selectable{{/if}} {{state}}" data-count="{{count}}" {{#if accessMode}}data-access="{{accessMode}}"{{/if}}>
    <a href="#" title="{{label}}">
        <span class="class-toggler clickable" tabindex="0"></span>
        <span class="icon-folder"></span>
        {{label}}
        <span class="selector clickable" tabindex="0"></span>
    </a>
    <ul>
    {{#if childList}}
        {{{childList}}}
    {{/if}}
    </ul>
    <div class="more hidden">
        <a href="#" class="btn-info small"><span class="icon-download"></span> {{__ 'Load more'}}</a>
    </div>
</li>
{{/equal}}

{{#equal type 'instance'}}
<li data-uri="{{uri}}" class="instance{{#if selectable}} selectable{{/if}} {{state}}"  {{#if accessMode}}data-access="{{accessMode}}"{{/if}}>
    <a href="#" title="{{label}}">
        <span class="icon-{{icon}}"></span>
        {{label}}
        <span class="selector clickable" tabindex="0"></span>
    </a>
</li>
{{/equal}}


