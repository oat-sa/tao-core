<div class="">
    <label>{{}}</label>
    <select class="" data-has-search="false">
        <option></option>{{!-- select2 needs an empty option for the placeholder --}}
        {{#each options}}
        <option value="{{id}}">{{label}}</option>
        {{/each}}
    </select>
</div>