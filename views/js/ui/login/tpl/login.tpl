<div class="xhtml_form login-component">
    <form action="{{url}}" method="post" id="{{name}}" name="{{name}}">
        <input type="hidden" class="global" name="{{name}}_sent" value="1">
        {{#if message.error}}
            <div class="xhtml_form_error">{{message.error}}</div>
        {{/if}}
        <div>
            <label class="form_desc" for="login">
                {{__ "Login"}}
            </label><input type="text" name="login" id="login" autofocus="autofocus"{{#if disableAutocomplete}} autocomplete="off"{{/if}}>
            {{#if fieldMessages.login}}
                <div class="form-error">
                    {{fieldMessages.login}}
                </div>
            {{/if}}
        </div>
        <div>
            <label class="form_desc" for="password">
                {{__ "Password"}}
            </label><input type="password" name="password" id="password"{{#if disableAutocomplete}} autocomplete="off"{{/if}}>
            {{#if fieldMessages.password}}
                <div class="form-error">
                    {{fieldMessages.login}}
                </div>
            {{/if}}
        </div>
        <div class="form-toolbar"><input type="submit" id="connect" name="connect" disabled="disabled" class="disabled" value="Log in"></div>
    </form>
</div>