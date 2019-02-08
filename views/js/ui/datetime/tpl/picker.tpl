<div class="datetime-picker {{setup}} {{#if triggerButton}}triggerMode{{/if}}">
    <input type="text"
        {{#if field.placeholder}}placeholder="{{field.placeholder}}"{{/if}}
        {{#if field.pattern}}pattern="{{field.pattern}}"{{/if}}
        {{#if field.name}}name="{{field.name}}"{{/if}}
        {{#if field.value}}value="{{field.value}}"{{/if}}
    />
    {{#if triggerButton}}
    <button type="button" class="picker-toggle" title="{{__ 'Toggle the date time picker'}}" aria-label="{{__ 'Toggle the date time picker'}}" >
        <span class="icon-calendar" role="img"></span>
    </button>
    <button type="button" class="picker-clear" title="{{__ 'Toggle the date time picker'}}" aria-label="{{__ 'Toggle the date time picker'}}">
        <span class="icon-reset" role="img"></span>
    </button>
    {{/if}}
</div>
