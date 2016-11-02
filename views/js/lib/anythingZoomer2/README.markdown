#AnythingZoomer jQuery Plugin

* Latest [AnythingZoomer demo](http://css-tricks.github.com/AnythingZoomer/).
* [Documentation](http://css-tricks.github.com/AnythingZoomer/use.html).
* [Original post](http://css-tricks.com/3075-anythingzoomer-jquery-plugin/) at CSS-Tricks.
* Have an issue? Submit it [here](https://github.com/CSS-Tricks/AnythingZoomer/issues).

## Known issues

* In the [text demo](http://css-tricks.github.com/AnythingZoomer/text.html), you can resize the large area content dynamically. At 2x, the top left corner of the large content matches the top left corner of the small content. But as the size increases (up to 4x), the spacing of the content from the top left corner increases. This happens with any content. I'm still looking for a fix.

## Changelog

### Version 2.2.1 (1/18/2013)
* New version labeled to enable updating the [jquery plugin registry](http://plugins.jquery.com/).

### Version 2.2 (10/31/2012)
* Fixed an issue with jQuery v1.8+:
  * jQuery 1.8 changed how the [box-sizing measured the width](http://blog.jquery.com/2012/08/16/jquery-1-8-box-sizing-width-csswidth-and-outerwidth/). Which returned a width of zero for the inner zoom content.
  * Changed the plugin to measure the width of the content children, so a zoom window should have its content wrapped or it may return an incorrect value.
  * See [issue #7](https://github.com/CSS-Tricks/AnythingZoomer/issues/7).
* Added a `delay` option:
  * Setting a delay, in milliseconds, will delay the time until the zoom window opens.
  * This is useful when a user quickly scrolls through the zoom area. 
  * See [issue #8](https://github.com/CSS-Tricks/AnythingZoomer/issues/8) for this feature request.

### Version 2.1.1 (7/3/2012)
* Fixed calendar demo links. The shortcut method was previously ignoring jQuery selectors: `$('#zoom').anythingZoomer('.day[rel=2009-08-26]');`.

### Version 2.1 (6/21/2012)
* Added a method to enable or disable AnythingZoomer.

    ```javascript
    $('.zoom').anythingZoomer('disable'); // disable AnythingZoomer
    $('.zoom').anythingZoomer('enable');  // enable AnythingZoomer
    ```

  or use the internal function directly or from a callback:

    ```javascript
    $('.zoom').data('zoomer').setEnabled(false); // disable AnythingZoomer
    $('.zoom').data('zoomer').setEnabled(true);  // enable AnythingZoomer
    ```

  The [double](http://css-tricks.github.com/AnythingZoomer/double.html) demo has been updated to show this new method. What isn't shown there is that when AnythingZoomer is disabled, the zoom window automatically closes and the small area content is shown.

### Version 2.0 (6/11/2012)
* AnythingZoomer can now be updated to change both the small and large content dynamically.
  * To update the content, just call anythingZoomer without any options: `$('#zoom').anythingZoomer();`.
  * Added a [Swap image](http://css-tricks.github.com/AnythingZoomer/swap.html) demo to show this in action!
* Modified the plugin to properly position the zoom window with dynamically centered content.
  * Added a `margin: 0 auto` to `.az-wrapper-inner` to center both the small and large content.
* A class name of `az-hovered` will be applied to the `az-small-inner` when it is hovered.
  * This can be used to change the opacity of the `smallArea` content while the zoom window is active.
  * See the anythingzoomer.css file; the addition is commented out.
* Added a `speed` option:
  * This option allows you to set the zoom window's fade animation speed.
  * Time can be set in milliseconds, or use `'slow'` or `'fast'`.
  * Default is 100 milliseconds.
* Added an `overlay` option:
  * An overlay has been added to cover the `smallArea` content, by default it has no styling and shouldn't interfere; but if it does, add a negative z-index to the `az-overly` (no "a") class.
  * If this option is `true`, the `az-overlay` class name is applied to the overlay to darken the area below the zoom window.
  * If `false`, the default setting, the overlay will remain transparent.
  * The [Image demo](http://css-tricks.github.com/AnythingZoomer/image.html) has been updated to demonstrate the overlay.
* Added events and callbacks:
  * `initialized` event which occurs after AnythingZoomer has finished initializing.
  * `zoom` event occurs when the zoom window is visible.
  * `unzoom` event occurs when the zoom window is hidden.
  * Instructions on how to use the callback or events can be found in the [documentation](http://css-tricks.github.com/AnythingZoomer/use.html).
* Added an `edit` option:
  * When true, it will add the mouse coordinates in the upper right corner of the zoom window.
  * If false, the default setting, no coordinates are shown.
  * Added to assign in finding the center of the zoom window for use in external zoom window links.
  * Resized the small Rushmore image in attempts to maintain a small-to-large content ratio of 2.5 - it makes the coordinates match better when using edit mode ;).
* Added `offsetX` and `offsetY` options:
  * When using the `edit` option, you may sometimes notice that the top left corner of the image isn't at 0,0 and the bottom right corner doesn't match the small area dimensions like it should be. This is partially due to the jQuery offset not including borders, margins or padding. And partially due to the ratio between the small and large areas.
  * Sometimes it isn't a big deal to be a few pixels off, but if you need to adjust it perfectly, use these options. To do this, enable the edit mode coordinates (set edit to true) then use these options to adjust the location of the large content within the zoom window to set the proper position.
  * Due to the calculation of the ratio between the small and large content, the bottom right corner may not perfectly coincide either.
* Updated `edge` option to now allow setting it to zero.
* Fixed an issue in the Double Demo when the Text Demo was expanded (large content showing), and the zoom window would not line up properly in the Image demo.
* Fixed an issue where moving from the AnythingZoomer window to an external link would cause a flicker.
* Updated the documentation page with the new options and features.

### Version 1.1.2 (12/9/2011)
* Added package.json created by Richard D. Worth
* Updated download links.

### Version 1.1 (8/25/2011)
* Initial commit to github.
* Modified initial required markup
* Removed `zoomPort` and `mover` options.
* Changed `smallArea` and `largeArea` to use classes.
* Removed the `speedMultiplier` option, it is now automatically calculated.
* Renamed `expansionSize` option to `edge`.
* Added `clone` option to make a clone of the `smallArea` content.
* Added methods to open up the zoom window from an external link.
* Added `switchEvent` option to allow changing the event that toggles between the small and large content.

### Version 1.0 (7/20/2009)
* Initial post on css-tricks
