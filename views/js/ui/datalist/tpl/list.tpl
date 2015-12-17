{{#each list}}
<tr data-id="{{id}}">
    {{#if ../selectable}}
    <td class="checkboxes"><input type="checkbox" name="cb[{{id}}]" value="1" /></td>
    {{/if}}
    <td class="label">{{label}}</td>
    {{#if ../actions}}
    <td class="actions">
        {{#each ../../actions}}
        <button class="btn-info small" data-control="{{id}}"{{#if title}} title="{{title}}"{{/if}}>
            {{#if icon}}<span class="icon icon-{{icon}}"></span>{{/if}}
            {{label}}
        </button>
        {{/each}}
    </td>
    {{/if}}
</tr>
{{/each}}
