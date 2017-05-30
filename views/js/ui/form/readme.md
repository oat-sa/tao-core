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
fieldFactory({
    label: string (required),
    hidden: boolean (optional),
    required: boolean (optional),
    validations: array[Object] (optional), // { predicate: msg }
    value: string (optional),
    widget: string (optional),
    uri: string (required)
});


// Methods


// Events
```