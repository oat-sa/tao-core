<input
    type="hidden"

    {{#if input.class}}
    class="{{input.class}}"
    {{/if}}

    {{#if input.name}}
    name="{{input.name}}"
    {{/if}}

    {{#if input.value}}
    value="{{input.value}}"
    {{/if}}>