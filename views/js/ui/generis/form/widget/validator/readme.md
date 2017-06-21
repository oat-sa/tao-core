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
.addValidation('alphaNumeric') // todo
.clearValidations()
.addValidation({
    predicate: /foobar/i,
    message: 'Must be foobar',
    precedence: 99
})
.run()
.display()
.clear();


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
validator.run();
validator.display();
validator.clear();
validator.addValidation(object validationOptions);
validator.clearValidations();
```