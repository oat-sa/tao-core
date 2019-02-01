<div id="{{id}}" class="feedback feedback-{{level}} {{#if popup}}popup{{/if}}">
    <span class="icon-{{level}}"></span>
    <div>
        {{{dompurify msg}}}
    </div>
    <span title="{{__ 'Close message'}}" class="icon-close" data-close=":parent .feedback"></span>
</div>
