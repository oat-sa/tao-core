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