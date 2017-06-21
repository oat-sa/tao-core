### ui/generis/validator/validator (is a ui/component)
```
/**
 * Examples
 */

var validator = generisValidatorFactory({
    validations: ['required']
})
.init()
.render('.ui-generis-validator-container')
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
var validator = generisValidatorFactory({
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