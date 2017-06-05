### ui/generis/form (is a ui/component)
```
// Initialization
var generisForm = generisFormFactory({
    class.uri: string (optional),
    class.label: string (optional),
    data.json: string (optional), // if given, data.url is optional
    data.url: string (required),
    uri: string (optional)
})
.init({
    form.action: string (optional),
    form.method: string (optional),
    submit.text: string (optional)
});


// Methods
{string|this} generisForm.get(fieldUri, [cb]);
{boolean|this} generisForm.load(data, [cb]);
{string|this} generisForm.set(fieldUri, value, [cb]);
{boolean|this} generisForm.validate([cb]);


// Events
generisForm.on('load');
```