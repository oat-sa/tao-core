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
.removeValidations()
.addValidation({
    predicate: /foobar/i,
    message: 'Must be foobar',
    precedence: 99
})
.run('foobar')
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
validator.run(string value);
validator.display();
validator.clear();
validator.addValidation(object validationOptions);
validator.removeValidations();
```