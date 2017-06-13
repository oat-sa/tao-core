### ui/generis/form (is a ui/component)
```
/**
 * Examples
 */

var form = generisFormFactory({
    widgets: [
        widgetFactory(...),
        widgetFactory(...)
    ]
})
.init({
    title: 'Form',
    submit: {
        text: 'Save'
    }
})
.addWidget({ ... })
.render('.ui-generis-form-container')
.addWidget([
    { ... },
    { ... }
])
.removeWidget('widgetUri')
.on('submit', function (e) {
    e.preventDefault();

    if ( this.validate() ) {
        request('http://tao.lu/tao/users/add', 'get', this.serializeArray())
        .then(function () {
            feedback().success();
        })
        .error(function (err) {
            feedback().error(err);
        });
    }

    return false;
});


/**
 * Api
 */

@returns ui/component
var form = generisFormFactory({
    widgets: array<ui/generis/form/widget> (optional)
})
.init({
    title: string (optional),
    submit.text: string (optional)
});

@type array<ui/generis/form/widget>
form.widgets;

@returns this
form.addWidget(object options || array<object> options);
form.removeWidget(string name);

@returns array<object>
form.serializeArray();

@returns boolean
form.validate();

@event submit
form.on('submit', function (event) {});
```