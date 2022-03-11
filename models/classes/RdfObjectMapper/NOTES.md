**WiP**

## Notes

- As Generis explicitly depends on `doctrine/annotations ~1.6.0`, the current
  implementation uses Doctrine annotations instead without needing additional
  deps.

- taoDockerize uses PHP 7.2 and Generis is supporting `"php": "^7.1"`: We cannot 
  use native PHP annotations (yet?).

- This draft provides an interface for an object mapper, and a single
  implementation that uses PHPDoc annotations.

- Right now only reading RDF data has been considered (i.e. not *writing* new
  data or updating existing data).

## Things to consider

- Maybe this should be in Generis instead?

- Another possibility would be to have a "root" object mapper that just
  delegates the mapping to a "child" mapper class (a chain of responsibility),
  so we can have a mapper based on Doctrine annotations while also having the
  possibility to implement a different one using PHP native annotations in the
  future (without needing to convert existing classes using Doctrine annotations
  to the PHP syntax all at once).
 
  - For that, we may use an approach similar to Doctrine's: if the object has a
    `@Whatever` annotation inside a PHPDoc block, we map using Doctrine
    annotations; if it has a native annotation (like
    `#[RdfResourceAttributeMapping(RdfResourceAttributeMapping::URI)]`), map

- May be worth considering how this fits with other architectural patterns
  currently under discussion.
