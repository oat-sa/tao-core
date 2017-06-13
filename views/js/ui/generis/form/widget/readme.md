### ui/generis/form/widget/widget (is a ui/component)
```
/**
 * Examples
 */

var widget = generisFormWidgetFactory({
    hidden: false,
    partial: hiddenBoxPtl,
    validator: generisFormWidgetValidatorFactory({ ... })
})
.setPartial(checkBoxPtl)
.init({
    label: 'Label',
    required: true
    value: 'FooBar',
    uri: 'tao.lu#label',
})
.render('.ui-generis-form-widget-container')
.hide()
.show()
.removeValidator()
.addValidator({ ... })
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
var widget = generisFormWidgetFactory({
    hidden: boolean (optional),
    partial: function (optional),
    validator: ui/generis/form/widget/validator (optional)
})
.init({
    label: string (required),
    required: boolean (optional),
    uri: string (required),
    ...
});

@type ui/generis/form/widget/validator
widget.validator;

@returns this
widget.setPartial(function partial);
widget.show();
widget.hide();
widget.addValidator(object options);
widget.removeValidator();

@returns string || array<string>
widget.get();
widget.set(string value);

@returns object || array<object>
widget.serialize();

@event change | blur
widget.on('change blur', function (event) {});
```