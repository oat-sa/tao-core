<ul class="breadcrumbs">
    {{#each breadcrumbs}}
    <li data-breadcrumb="{{id}}">
        {{#if url}}
        <a href="{{url}}">{{label}}{{#if data}} - {{data}}{{/if}}</a>
        {{else}}
        <span class="a">{{label}}{{#if data}} - {{data}}{{/if}}</span>
        {{/if}}
        {{#if entries}}
        <ul class="entries">
            {{#each entries}}
            <li data-breadcrumb="{{id}}">
                <a href="{{url}}">{{label}}{{#if data}} - {{data}}{{/if}}</a>
            </li>
            {{/each}}
        </ul>
        {{/if}}
    </li>
    {{/each}}
</ul>
