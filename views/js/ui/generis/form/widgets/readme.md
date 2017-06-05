### [prototype] ui/generis/form/widgets/_widget (is a ui/component)
```
// Initialization
var generisFormWidget = generisFormWidgetFactory({
    validationContainer: string (optional),
    validations: array (optional)
})
.init({
    hidden: boolean (optional),
    required: boolean (optional)
    // other template variables
});


// Methods
{sting|this} generisFormWidget.get([cb]);
{boolean|this} generisFormWidget.set(value, [cb]);
{Array|this} generisFormWidget.validate([cb]);


// Events
generisFormWidget.on('change blur', function (event) {});
```