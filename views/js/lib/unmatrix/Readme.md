
# unmatrix

  Parse and normalize the individual values of a css transform.
  Uses the same algorithm that Firefox uses to parse the transform string.
  Only parses the 2D transform attributes. This library is intentionally low-level
  and should probably be used within another library.

  If you'd like to parse 3D transforms take a look at:
  http://www.w3.org/TR/css3-transforms/#matrix-decomposing.

## Example

```js
var unmatrix = require('unmatrix');
var t = unmatrix(box);
box.style.webkitTransform = toString(t);
```

## Installation

  Install with [component(1)](http://component.io):

    $ component install matthewmueller/unmatrix

## API

### unmatrix(el)

Parse the individual values of a CSS transform. Returns the following object:

```js
{
  "translateX": 400, // px
  "translateY": 200, // px
  "rotate": 60, // deg
  "skew": 20, // deg
  "scaleX": 2,
  "scaleY": 2
}
```

## TODO

- support for transform matrix parsing
- tests would be nice

## License

  MIT
