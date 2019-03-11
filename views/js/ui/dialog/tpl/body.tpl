<div class="preview-modal-feedback modal {{class}}">
    <div class="modal-body clearfix">
        {{#if heading}}
        <h4 class="strong">{{heading}}</h4>
        {{/if}}

        <p class="message">{{{message}}}</p>

        {{#if content}}
        <div class="content">{{{content}}}</div>
        {{/if}}

        <div class="buttons rgt"></div>
    </div>
</div>
