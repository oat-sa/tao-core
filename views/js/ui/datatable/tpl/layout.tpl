<div class="datatable-wrapper">

    {{#if options.filter}}
    <aside class="filter" data-column="{{options.filter.columns}}">
        <input type="text" value="" name="filter" placeholder="{{__ 'Filter'}}">
        <button class="icon-find" type="button"></button>
    </aside>
    {{/if}}

    {{#with options.status}}
    <h2>
        <span class="empty-list hidden">{{#if empty}}{{empty}}{{else}}{{__ 'Nothing to list!'}}{{/if}}</span>
        <span class="available-list hidden"><span class="text">{{#if available}}{{available}}{{else}}{{__ 'Available'}}{{/if}}</span>: <span class="count">{{count}}</span></span>
        <span class="loading">{{#if loading}}{{loading}}{{else}}{{__ 'Loading'}}{{/if}}...</span>
    </h2>
    {{/with}}

    {{#if options.tools}}
    <aside class="action-bar clearfix">
        {{#each options.tools}}
            <button class="btn-info small tool-{{#if id}}{{id}}{{else}}{{@key}}{{/if}}{{#if massAction}} invisible{{/if}}"{{#if title}} title="{{title}}"{{/if}}>
                <span class="icon-{{#if icon}}{{icon}}{{else}}{{#if id}}{{id}}{{else}}{{@key}}{{/if}}{{/if}}"></span> {{#if label}}{{label}}{{else}}{{#unless id}}{{@key}}{{/unless}}{{/if}}
            </button>
        {{/each}}
    </aside>
    {{/if}}

    <div class="datatable-pagination-top"></div>

    <div class="datatable-container">
        <table class="matrix datatable">
            <colgroup>
                {{#if options.selectable}}
                <col/>
                {{/if}}
                {{#each options.model}}
                <col/>
                {{/each}}
                {{#if options.showActions}}
                    {{#if ../options.actions}}
                </col>
                    {{/if}}
                {{/if}}
            </colgroup>
            <thead>
                <tr>
                    {{#if options.selectable}}
                    <th class="checkboxes"><input type="checkbox" name="checkall" value="1" /></th>
                    {{/if}}
                    {{#each options.model}}
                    <th>
                        <div {{#if sortable}}data-sort-by="{{id}}"{{/if}}>{{label}}</div>
                        {{#if filterable}}
                        <aside data-column="{{id}}" class="filter column
                            {{#if customFilter}} customInput" >
                                {{{customFilter.template}}}
                            {{else}} ">
                                <input type="text" value="" name="filter[{{id}}]" placeholder="{{filterable.placeholder}}">
                                <button class="icon-find" type="button"></button>
                            {{/if}}
                        </aside>
                        {{/if}}
                    </th>
                    {{/each}}
                    {{#if options.showActions}}
                        {{#if ../options.actions}}
                    <th class="actions">{{__ 'Actions'}}</th>
                        {{/if}}
                    {{/if}}
                </tr>
            </thead>
            <tbody>
                {{#each dataset.data}}
                    <tr data-item-identifier="{{id}}">
                        {{#if ../options.selectable}}
                        <td class="checkboxes"><input type="checkbox" name="cb[{{id}}]" value="1" /></td>
                        {{/if}}

                        {{#each ../options.model}}
                            {{#if type}}
                            <td class="{{id}}">
                                {{#each actions}}

                                    {{#if id}}
                                        {{#with ../../this}}
                                            {{#unless ../hidden}}
                                                {{#with ../../this}}
                                <button class="btn-info small {{id}}"{{#if title}} title="{{title}}"{{/if}}>{{#if icon}}<span class="icon-{{icon}}"></span> {{/if}}{{#if label}}{{label}}{{/if}}</button>
                                                {{/with}}
                                            {{/unless}}
                                        {{/with}}
                                    {{else}}
                                <button class="btn-info small {{@key}}"{{#if title}} title="{{title}}"{{/if}}>{{#if icon}}<span class="icon-{{icon}}"></span> {{/if}}{{#if label}}{{label}}{{/if}}</button>
                                    {{/if}}

                                {{/each}}
                            </td>
                            {{else}}
                            <td class="{{id}}">{{{property id ../../this}}}</td>
                            {{/if}}

                        {{/each}}

                        {{#if ../options.showActions}}
                            {{#if ../../options.actions}}
                        <td class="actions">
                                {{#each ../../../options.actions}}
                                    {{#if id}}
                                        {{#with ../../this}}
                                            {{#unless ../hidden}}
                                                {{#with ../../this}}
                            <button class="btn-info small {{id}}"{{#if title}} title="{{title}}"{{/if}}><span class="icon-{{#if icon}}{{icon}}{{else}}{{id}}{{/if}}"></span> {{label}}</button>
                                                {{/with}}
                                            {{/unless}}
                                        {{/with}}
                                    {{else}}
                            <button class="btn-info small {{@key}}"><span class="icon-{{@key}}"></span> {{@key}}</button>
                                    {{/if}}
                                {{/each}}
                        </td>
                            {{/if}}
                        {{/if}}
                    </tr>
                {{/each}}
            </tbody>
        </table>
    </div>
    <div class="datatable-pagination-bottom"></div>
</div>
