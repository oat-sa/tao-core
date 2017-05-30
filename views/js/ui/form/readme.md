### ui/form/form (is a ui/component)
```
// Initialization
var form = formFactory({
    action: string (required),
    json: object (optional),
    method: string (optional),
    name: string (optional),
    request: object (optional),
    templateVars.submit.value: string (optional)
});


// Methods
var field = form.getField(name);

form = form.addField(name, fieldOptions);

form = form.removeField(name);

var isValid = form.validate();


// Events
form.on('error', function (error) {});
form.on('load', function (data) {});
form.on('success', function (data) {});
```


### ui/form/field (is a ui/component)
```
// Initialization
var field = fieldFactory({
    templateVars: object (optional),
    validations: array (optional),
    widget: function (required)
});


// Methods
var name = field.getName();
var value = field.getValue();
field = field.addValidation(/.{1,}/, 'Must contain value');
field = field.clearValidations();
var isValid = field.isValid();
var isValid = field.validate();


// Events
```