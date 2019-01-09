<div class="xhtml_form">
    <form action="{{action}}" method="post" id="{{name}}" name="{{name}}">
        <input type="hidden" class="global" name="{{name}}_sent" value="1">
        {{#if error}}
        <div class="xhtml_form_error">{{error}}</div>
        {{/if}}
        <div>
            <label class="form_desc" for="login">
                {{__ "Login"}}
            </label>
            <input type="text" name="login" id="login" autofocus="autofocus"{{#if disableAutocomplete}} autocomplete="off"{{/if}} value="{{disableAutocomplete}}">
        </div>
        <div>
            <label class="form_desc" for="password">
                {{__ "Password"}}
            </label>
            <input type="password" name="password" id="password"{{#if disableAutocomplete}} autocomplete="off"{{/if}} value="{{enablePasswordReveal}}">
        </div>
    </form>
</div>