### ui/generis/form (is a ui/component)
```
// Initialization
var generisForm = generisFormFactory({
    uri: string (optional)
    class.uri: string (required)
});


// Methods
{string|this} generisForm.get(fieldUri, [cb]);
{boolean|this} generisForm.set(fieldUri, value, [cb]);
{boolean|this} generisForm.validate([cb]);
{object|this} generisForm.submit([cb]);


// Events
generisForm.on('submit', function (event) {});
```