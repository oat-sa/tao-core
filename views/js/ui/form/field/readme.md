### ui/form/field

```
fieldFactory({
    label: string (required),
    uri: string (required),
    value: string (optional),
    template: jQuery|string (required),
    templateVars: array[string] (optional),
    validations: array[{ string|regex|function: string }] (optional),
    hidden: boolean (optional)
});
```