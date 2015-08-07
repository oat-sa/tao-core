{{#if tools}}
<aside class="action-bar">
    {{#each ../tools}}

    <button class="btn-info small tool-{{#if id}}{{id}}{{else}}{{@key}}{{/if}}{{#if massAction}} invisible{{/if}}"{{#if title}} title="{{title}}"{{/if}}><span class="icon-{{#if icon}}{{icon}}{{else}}{{#if id}}{{id}}{{else}}{{@key}}{{/if}}{{/if}}"></span> {{#if label}}{{label}}{{else}}{{#unless id}}{{@key}}{{/unless}}{{/if}}</button>

    {{/each}}
</aside>
{{/if}}
<div class="grid-row">
    <div class="col-6">{{__ 'Page'}} <strong>{{page}}</strong> {{__ 'of'}} {{total}}</div>
    <div class="col-6 txt-rgt">
        <button class="btn-info small datatable-backward"><span class="icon-backward"></span>{{__ 'Previous'}}</button>
        <button class="btn-info small datatable-forward">{{__ 'Next'}}<span class="icon-forward r"></span></button>
    </div>
</div>
<div class="datatable-container">
    <table class="matrix datatable">
        <colgroup>
            {{#if selectable}}
            <col/>
            {{/if}}
            <col/>
            {{#model}}
            <col/>
            {{/model}}
        </colgroup>
        <thead>
            <tr>
                {{#if selectable}}
                <th class="checkboxes"><input type="checkbox" name="checkall" value="1" /></th>
                {{/if}}
                <th class="id"></th>
                {{#model}}
                <th {{#if sortable}}data-sort-by="{{id}}"{{/if}}>{{label}}</th>
                {{/model}}
                {{#if actions}}
                <th class="actions">{{__ 'Actions'}}</th>
                {{/if}}
            </tr>
        </thead>
        <tbody>
            {{#data}}
                <tr data-item-identifier="{{id}}">
                    {{#if ../selectable}}
                    <td class="checkboxes"><input type="checkbox" name="cb[{{id}}]" value="1" /></td>
                    {{/if}}
                    {{#each ../model}}
                    <td class="{{id}}">{{property id ../this}}</td>
                    {{/each}}
                    {{#if ../actions}}
                    <td class="actions">
                        {{#each ../../actions}}

                        <button class="btn-info small {{#if id}}{{id}}{{else}}{{@key}}{{/if}}"{{#if title}} title="{{title}}"{{/if}}><span class="icon-{{#if icon}}{{icon}}{{else}}{{#if id}}{{id}}{{else}}{{@key}}{{/if}}{{/if}}"></span> {{#if label}}{{label}}{{else}}{{#unless id}}{{@key}}{{/unless}}{{/if}}</button>

                        {{/each}}
                    </td>
                    {{/if}}
                </tr>
            {{/data}}
        </tbody>
    </table>
</div>
<div class="grid-row" style="margin-top:20px;">
    <div class="col-6">{{__ 'Page'}} <strong>{{page}}</strong> {{__ 'of'}} {{total}}</div>
    <div class="col-6 txt-rgt">
        <button class="btn-info small datatable-backward"><span class="icon-backward"></span>{{__ 'Previous'}}</button>
        <button class="btn-info small datatable-forward">{{__ 'Next'}}<span class="icon-forward r"></span></button>
    </div>
</div>
