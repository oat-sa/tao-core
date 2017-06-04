### ui/generis/form (is a ui/component)
```
// Initialization
var generisForm = generisFormFactory({
    class.uri: string (required),
    class.label: string (optional),
    request.url: string (required),
    uri: string (optional)
});


// Methods
{string|this} generisForm.get(fieldUri, [cb]);
{string|this} generisForm.set(fieldUri, value, [cb]);
{boolean|this} generisForm.validate([cb]);
{object|this} generisForm.submit([cb]);


// Events
generisForm.on('submit', function (event) {});
```