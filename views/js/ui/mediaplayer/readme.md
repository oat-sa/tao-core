## Custom Media Player

### Use

```javascript
require(['ui/mediaplayer'], function(mediaplayer) {
    var player = mediaplayer({
        // options
    });
});
```

### Options

- *String* **type** - The type of media to play
- *String* / *Array* **url** - The URL to the media
- *String* / *jQuery* / *HTMLElement* **renderTo** - An optional container in which renders the player
- *Boolean* **loop** - The media will be played continuously
- *Boolean* **canPause** - The play can be paused
- *Boolean* **startMuted** - The player should be initially muted
- *Boolean* **autoStart** - The player starts as soon as it is displayed
- *Number* **autoStartAt** - The time position at which the player should start
- *Number* **maxPlays** - Sets a few number of plays (default: infinite)
- *Number* **volume** - Sets the sound volume (default: 80)
- *Number* **width** - Sets the width of the player (default: depends on media type)
- *Number* **height** - Sets the height of the player (default: depends on media type)
- *Function* **onrender** - Event listener called when the player is rendering
- *Function* **onready** - Event listener called when the player is fully ready
- *Function* **onplay** - Event listener called when the playback is starting
- *Function* **onupdate** - Event listener called while the player is playing
- *Function* **onpause** - Event listener called when the playback is paused
- *Function* **onended** - Event listener called when the playback is ended
- *Function* **onlimitreached** - Event listener called when the play limit has been reached
- *Function* **ondestroy** - Event listener called when the player is destroying

### Events

Some events are triggered both on the component using `eventifier` and on the DOM through the component wrapper.
The events triggered through the DOM are namespaced like this: `eventname.mediaplayer`.

- **render** / **render.mediaplayer**

    Triggered when the component is rendered. The DOM event is only triggered when the target container is provided.

    ###### Params:

    - *jQuery* **$component**
    - *mediaplayer* **player**


- **ready** / **ready.mediaplayer**

    Triggered when the component is ready to play the media.

    ###### Params:

    - *mediaplayer* **player**


- **destroy** / **destroy.mediaplayer**

    Triggered when the component is destroying

    ###### Params:

     - *mediaplayer* **player**


- **play** / **play.mediaplayer**

    Triggered when the component is playing the media

    ###### Params:

     - *mediaplayer* **player**


- **pause** / **pause.mediaplayer**

    Triggered when the media has been paused

    ###### Params:

     - *mediaplayer* **player**


- **ended** / **ended.mediaplayer**

    Triggered when the media playback has ended

    ###### Params:

     - *mediaplayer* **player**


- **limitreached** / **limitreached.mediaplayer**

    Triggered when the play limit has been reached

    ###### Params:

     - *mediaplayer* **player**


- **update** / **update.mediaplayer**

    Triggered each time the player need to update its status

    ###### Params:

     - *mediaplayer* **player**


### Api

- *mediaplayer* **player.init([config])**

    Initializes the media player. This method is called by the factory, so you won't need to call it directly.


- *mediaplayer* **player.destroy()**

    Uninstalls the media player.


- *jQuery* **player.render([to])**

    Renders the media player according to the media type

    - *String* / *jQuery* / *HTMLElement* **to** - An optional container in which renders the component


- *mediaplayer* **player.seek(time)**

    Sets the start position inside the media

    - *Number* **time** - The start position in seconds


- *mediaplayer* **player.play([time])**

    Play the media. You can set the time offset.

    - *Number* **time** - Optional start position in seconds


- *mediaplayer* **player.pause([time])**

    Pause the media. You can set the time offset.

    - *Number* **time** - Optional start position in seconds


- *mediaplayer* **player.resume()**

    Resume the media from a pause


- *mediaplayer* **player.stop()**

    Stops the playback


- *mediaplayer* **player.restart()**

    Restarts the media from the beginning


- *mediaplayer* **player.rewind()**

    Rewind the media to the beginning


- *mediaplayer* **player.mute([time])**

    Mutes the media

    - *Boolean* **flag** - A flag to set the mute state (default: true)


- *mediaplayer* **player.unmute()**

    Restore the sound of the media after a mute


- *mediaplayer* **player.setVolume(value)**

    Sets the sound volume of the media being played

    - *Number* **value** - A value between 0 and 100


- *Number* **player.getVolume()**

    Gets the sound volume of the media being played, as a value between 0 and 100


- *Number* **player.getPosition()**

    Gets the current displayed position inside the media, in seconds


- *Number* **player.getDuration()**

    Gets the duration of the media, in seconds


- *Number* **player.getTimesPlayed()**

    Gets the number of times the media has been played


- *jQuery* **player.getType()**

    Gets the type of player


- *jQuery* **player.getDom()**

    Gets the underlying DOM element


- *Array* **player.getSources()**

    Gets the list of media.


- *mediaplayer* **player.addSource(src, [type])**

    Adds a media source to the player.

    - *String* / *Object* **src** - The media URL, or an object containing the source and the type
    - *String* **type** - The media MIME type


- *mediaplayer* **player.setSource(src, [type])**

    Sets the media source. If a source has been already set, it will be replaced.

    - *String* / *Object* **src** - The media URL, or an object containing the source and the type
    - *String* **type** - The media MIME type


- *Boolean* **player.is(state)**

    Tells if the media is in a particular state.

    - *String* **state**


- *mediaplayer* **player.resize(width, height)**

    Changes the size of the player

    - *Number* **width**
    - *Number* **height**


- *mediaplayer* **player.enable()**

    Enables the media player


- *mediaplayer* **player.disable()**

    Disables the media player


- *mediaplayer* **player.show()**

    Shows the media player


- *mediaplayer* **player.hide()**

    Hides the media player


- **player.execute(command)**

    Executes a command onto the media.

    - *String* **command**


- *mediaplayer* **player.on(eventName, eventHandler)**

    Binds a particular event with a handler.

    - *String* **eventName**
    - *Function* **eventHandler**


- *mediaplayer* **player.off(eventName)**

    Removes all handlers bound with a particular event

    - *String* **eventName**


- *mediaplayer* **player.trigger(eventName)**

    Triggers an event. All arguments following the eventName will be forwarded to each called handler.

    - *String* **eventName**
