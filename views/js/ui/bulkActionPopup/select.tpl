<span class="cascading-combo-box">
    <label>{{comboboxLabel}}</label>
    <select class="" data-id="{{comboboxId}}" data-has-search="false">
        <option></option>{{!-- select2 needs an empty option for the placeholder --}}
        {{#each options}}
        <option value="{{id}}" data-categories="{{categories}}">{{label}}</option>
        {{/each}}
    </select>
</span>