### ui/form/form is a ui/component
```
formFactory({
    action: {
        url: string (optional),
        method: string (optional),
        parameters: object (optional)
    },
    fields: array(ui/form/field) (optional),
    container: string|jQuery (required),
    success: function (required),
    error: function (required)
});
```

### ui/form/field is a ui/component
```
fieldFactory({
    label: string (required),
    hidden: boolean (optional),
    required: boolean (optional),
    validations: array[Object] (optional), // { predicate: msg }
    value: string (optional),
    widget: string (optional),
    uri: string (required)
});
```