<div class="preview-modal-feedback modal">
    <div class="modal-body clearfix">
        {{#if heading}}
        <h4 class="strong">{{{heading}}}</h4>
        {{/if}}

        <p class="message">{{message}}</p>

        {{#if content}}
        <div class="content">{{{content}}}</div>
        {{/if}}

        {{#if checkbox}}
        <label for="dont-show-again">
            <input type="checkbox" id="dont-show-again" name="dont-show-again" {{#if checkbox.checked}}checked{{/if}} />
            <span class="icon-checkbox"></span>
            {{{checkbox.text}}}
        </label>
        {{/if}}

        <div class="buttons rgt"></div>
    </div>
</div>
