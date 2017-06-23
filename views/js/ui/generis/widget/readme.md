### ui/generis/widget/widget (is a ui/component)
```
/**
 * Examples
 */

var widget = generisWidgetFactory({
    partial: hiddenBoxPtl,
    validator: generisValidatorFactory({ ... })
})
.setPartial(checkBoxPtl)
.init({
    label: 'Label',
    required: true
    value: 'FooBar',
    uri: 'tao.lu#label',
})
.render('.ui-generis-widget-container')
.removeValidator()
.addValidator({ ... })
.validate()
.on('change blur', function (e) {
    this.validator.run;
    if (this.validator.errors.length) {
        console.log(this.get());
        this.set('ummm...');
    } else {
        console.log(this.serialize());
    }
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
widget.setPartial(function partial);
widget.addValidator(object options);
widget.removeValidator();
widget.validate();

@returns string || array<string>
widget.get();
widget.set(string value);

@returns object || array<object>
widget.serialize();

@event change | blur
widget.on('change blur', function (event) {});
```