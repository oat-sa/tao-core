<div class="datetime-picker {{setup}} {{#if controlButtons}}control-buttons{{/if}}">
    <input type="text" disabled
        {{#if field.id}}id="{{field.id}}"{{/if}}
        {{#if field.name}}name="{{field.name}}"{{/if}}
        {{#if field.value}}value="{{field.value}}"{{/if}}
        {{#if field.placeholder}}placeholder="{{field.placeholder}}"{{/if}}
        {{#if field.pattern}}pattern="{{field.pattern}}"{{/if}}
        {{#if field.label}}aria-label="{{field.label}}"{{/if}}
    />
    {{#if controlButtons}}
    <button type="button" disabled class="picker-toggle" title="{{__ 'Toggle the date time picker'}}" aria-label="{{__ 'Toggle the date time picker'}}" >
        <span class="icon-calendar" role="img"></span>
    </button>
    <button type="button" disabled class="picker-clear" title="{{__ 'Clear the date time picker'}}" aria-label="{{__ 'Clear the date time picker'}}">
        <span class="icon-reset" role="img"></span>
    </button>
    {{/if}}
</div>
