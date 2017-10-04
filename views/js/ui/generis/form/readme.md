### ui/generis/form (is a ui/component)
```
/**
 * Examples
 */

var form = generisFormFactory({
    class: {},
    properties: [],
    values: {}
}, {
    formAction: '#',
    formMethod: 'get',
    submitText: 'Save',
    title: 'Generis Form'
})
.addWidget(widgetData)
.removeWidget(widgetData.uri)
.render('.container')
.on('submit', function () {
    var self = this;

    e.preventDefault();

    this.validate();

    if (!this.errors.length) {
        this.setState('thinking');
        request(endpoint, method, this.serializeArray())
        .then(function () {
            self.setState('thinking', false);
            feedback().success('Submit successful');
        })
        .error(function (err) {
            self.setState('thinking', false);
            feedback().error(err);
        })
    }

    return false;
})


/**
 * Api
 */

@returns ui/component
var form = generisFormFactory(object options, object config);

@type object
form.data

@type string[]
form.errors;

@type ui/generis/widget[]
form.widgets;

@returns this
form.addWidget(object widgetData);
form.removeWidget(string name);
form.validate();

@returns object[]
form.serializeArray();

@event submit
form.on('submit', function () {});
```