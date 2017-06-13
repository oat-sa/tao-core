### ui/generis/form/widget/validator/validator (is a ui/component)
```
/**
 * Examples
 */

var validator = generisFormWidgetValidatorFactory({
    validations: ['required']
})
.init()
.render('.ui-generis-form-widget-validator-container')
.addValidation('alphaNumeric')
.removeValidation('required')
.addValidation('foobar', {
    predicate: /foobar/i,
    message: 'Must be foobar',
    precedence: 99
})
.run()
.show()
.hide()
.clear();

return validator.errors;


/**
 * Api
 */

@return ui/component
var validator = generisFormWidgetValidatorFactory({
    validations: [ ... ]
})
.init();

@type array<string>
validator.errors;

@type array<object>
validator.validations;

@returns this
validator.show();
validator.hide();
validator.run();
validator.clear();
validator.addValidation(string name, object options (optional));
validator.removeValidation(name);
```