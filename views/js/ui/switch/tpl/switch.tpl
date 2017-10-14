<div class="switch" title="{{title}}">
    <input type="checkbox" name="{{name}}" {{#if on.active}}checked{{/if}}>
    <label>
        <span class="off {{#if off.active}}active{{/if}}">{{off.label}}</span>
        <span class="on  {{#if on.active}}active{{/if}}">{{on.label}}</span>
    </label>
</div>
