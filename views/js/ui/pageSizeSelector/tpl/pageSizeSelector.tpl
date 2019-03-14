<div class="page-size-selector-container">
    <select class="select2">
        {{#each options}}
        <option {{#if selected}}selected{{/if}} value="{{value}}">
            {{label}}
        </option>
        {{/each}}
    </select>
</div>
