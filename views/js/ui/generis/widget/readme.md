### ui/generis/widget/widget (is a ui/component)
```
/**
 * Examples
 */

var widget = generisWidgetFactory({
    validator: generisValidatorFactory({ ... })
})
.init({
    label: 'Label',
    required: true
    value: 'FooBar',
    uri: 'tao.lu#label',
    ...
})
.render('.ui-generis-widget-container')
.setValidator(validator)
.validate()
.on('change blur', function (e) {
    this.validator.run();
    console.log(this.serialize());
});


/**
 * Api
 */

@returns ui/component
var widget = generisWidgetFactory({
    partial: function (optional),
    validator: ui/generis/validator/validator (optional)
})
.init({
    label: string (required),
    required: boolean (optional),
    uri: string (required),
    ...
});

@type ui/generis/validator/validator
widget.validator;

@returns this
widget.setValidator(object validator);
widget.validate();

@returns string || array<string>
widget.get();
widget.set(string value);

@returns object || array<object>
widget.serialize();

@event change | blur
widget.on('change blur', function (event) {});
```